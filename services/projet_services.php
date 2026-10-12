<?php
// services/projet_services.php - Version V4.0.0 Collaborative Pure MVC (Livraison Officielle)

/**
 * 1. Récupère tous les projets accessibles par l'utilisateur connecté 
 * (Ses projets personnels + les projets partagés via ses groupes de confiance)
 */
function getTousProjets($lien) {
    $id_session_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

    // Si Super-Admin, il a une visibilité absolue sur tous les projets de la plateforme
    if ($role === 'admin') {
        $sql = "SELECT id, nom_projet, utilisateur_id, groupe_id FROM todo_projets ORDER BY nom_projet ASC";
        $stmt = $lien->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🎯 ALGORITHME COLLABORATIF ET ÉTANCHE V4.0.0
    // Un utilisateur voit ses propres projets OR les projets rattachés à un groupe dont il est membre
    $sql = "
        SELECT DISTINCT pr.id, pr.nom_projet, pr.utilisateur_id, pr.groupe_id
        FROM todo_projets pr
        LEFT JOIN todo_groupe_membres gm ON pr.groupe_id = gm.groupe_id
        WHERE pr.utilisateur_id = ? 
           OR gm.utilisateur_id = ?
        ORDER BY pr.nom_projet ASC
    ";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute([$id_session_user, $id_session_user]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 2. Récupère un projet spécifique par son ID (en vérifiant les droits d'accès)
 */
function getProjetParId($lien, $projet_id) {
    $id_session_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    if ($role === 'admin') {
        $query = "SELECT id, nom_projet FROM todo_projets WHERE id = ? LIMIT 1";
        $stmt = $lien->prepare($query);
        $stmt->execute([(int)$projet_id]);
    } else {
        $query = "SELECT pr.id, pr.nom_projet 
                  FROM todo_projets pr 
                  LEFT JOIN todo_groupe_membres gm ON pr.groupe_id = gm.groupe_id
                  WHERE pr.id = ? AND (pr.utilisateur_id = ? OR gm.utilisateur_id = ?) 
                  LIMIT 1";
        $stmt = $lien->prepare($query);
        $stmt->execute([(int)$projet_id, $id_session_user, $id_session_user]);
    }
    
    $projet = $stmt->fetch();
    return $projet ? $projet : null;
}

/**
 * 3. Récupère l'historique de TOUS les projets partagés avec un utilisateur (lu ou non lu)
 * Amélioration V4.0.0 : Ajout du DISTINCT pour stabiliser le compteur de l'en-tête bleu
 */
function getHistoriqueProjetsPartages($lien, $id_utilisateur) {
    $sql = "
        SELECT pr.id, pr.nom_projet, COUNT(DISTINCT t.id) AS nb_taches
        FROM todo_projets pr
        INNER JOIN todo_groupe_membres gm ON pr.groupe_id = gm.groupe_id
        LEFT JOIN todo_taches t ON t.projet_id = pr.id
        WHERE gm.utilisateur_id = ? 
          AND pr.utilisateur_id != ?
        GROUP BY pr.id
        ORDER BY pr.nom_projet ASC
    ";
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$id_utilisateur, (int)$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 4. Récupère les cercles d'un utilisateur et la liste des AUTRES membres de chaque cercle
 * Utile pour l'affichage explicite des collaborateurs invités sous le formulaire de création
 */
function getGroupesUtilisateur($lien, $id_utilisateur) {
    $sql = "
        SELECT g.id, g.nom_groupe, 
               GROUP_CONCAT(u.name SEPARATOR ', ') AS collegues
        FROM todo_groupes g
        INNER JOIN todo_groupe_membres gm ON g.id = gm.groupe_id
        INNER JOIN todo_groupe_membres gm2 ON g.id = gm2.groupe_id
        INNER JOIN portail_users u ON gm2.utilisateur_id = u.id
        WHERE gm.utilisateur_id = ? 
          AND gm2.utilisateur_id != ?
        GROUP BY g.id
        ORDER BY g.nom_groupe ASC
    ";
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$id_utilisateur, (int)$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 5. Récupère la liste des nouveaux projets partagés non lus par un utilisateur
 * AMÉLIORATION V4.0.0 : Extraction du nom de l'auteur original pour l'ergonomie du bandeau
 */
function getNouveauxProjetsPartages($lien, $id_utilisateur) {
    $sql = "
        SELECT pr.id, pr.nom_projet, 
               u_owner.name AS nom_createur, -- 🎯 Récupère le nom de l'auteur original
               COUNT(DISTINCT t.id) AS nb_taches
        FROM todo_projets pr
        INNER JOIN todo_groupe_membres gm ON pr.groupe_id = gm.groupe_id
        INNER JOIN portail_users u_owner ON pr.utilisateur_id = u_owner.id -- Jointure auteur
        LEFT JOIN todo_taches t ON t.projet_id = pr.id
        WHERE gm.utilisateur_id = ? 
          AND pr.utilisateur_id != ?
          AND NOT EXISTS (
              SELECT 1 FROM todo_notifications_lectures nl 
              WHERE nl.projet_id = pr.id AND nl.utilisateur_id = ?
          )
        GROUP BY pr.id, u_owner.name
    ";
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$id_utilisateur, (int)$id_utilisateur, (int)$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * 6. Enregistre l'acquittement de lecture d'un projet partagé par un utilisateur
 * CORRECTIF [V4.0.0] : Élimination du mot anglais project_id pour débloquer le compteur
 */
function marquerProjetCommeLu($lien, $id_utilisateur, $projet_id) {
    $sql = "INSERT IGNORE INTO todo_notifications_lectures (utilisateur_id, projet_id) VALUES (?, ?)";
    $stmt = $lien->prepare($sql);
    return $stmt->execute([(int)$id_utilisateur, (int)$projet_id]);
}

/**
 * 7. Récupère la liste de tous les participants d'un groupe à partir de l'ID d'un projet
 */
function getParticipantsParProjet($lien, $projet_id) {
    $sql = "
        SELECT u.id, u.name AS nom 
        FROM portail_users u
        INNER JOIN todo_groupe_membres gm ON u.id = gm.utilisateur_id
        INNER JOIN todo_projets pr ON gm.groupe_id = pr.groupe_id
        WHERE pr.id = ?
        ORDER BY u.name ASC
    ";
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$projet_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

