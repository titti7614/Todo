<?php
// services/lister_taches.php - Version V4.0.0 Collaborative Stable (Livraison Officielle)

/**
 * 1. Récupère les tâches d'un projet avec filtrage collaboratif fluide et masquage strict.
 */
function getTachesParProjet($lien, $projet_id, $categories_id = [], $recherche_texte = '', $uniquement_les_miennes = false) {
    $id_session_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; 
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user'; 
    
    $sql = "SELECT t.id, t.texte, t.statut, t.note, t.date_echeance, t.attribue_a, t.utilisateur_id AS createur_tache_id,
                   u.username AS nom_assigne,
                   GROUP_CONCAT(c.nom_categorie SEPARATOR '|||') AS nom_categories, 
                   GROUP_CONCAT(c.couleur SEPARATOR '|||') AS couleur_categories 
            FROM todo_taches t
            LEFT JOIN todo_projets pr ON t.projet_id = pr.id
            LEFT JOIN portail_users u ON t.attribue_a = u.id
            LEFT JOIN todo_tache_categories tc ON t.id = tc.tache_id
            LEFT JOIN todo_categories c ON tc.categorie_id = c.id
            WHERE t.projet_id = ?";
            
    $params = [(int)$projet_id];
    
    // ======================================================================
    // 🎯 ALGORITHME DE SECTORISATION COLLABORATIVE ÉTANCHE (V4.0.0)
    // ======================================================================
    if ($uniquement_les_miennes) {
        // 👥 BOUTON BLEU "VOIR TOUTE L'ÉQUIPE" ACTIF À L'ÉCRAN
        // Règle d'or : Uniquement le travail partagé ou délégué au sein du groupe.
        $sql .= " AND t.attribue_a IS NOT NULL";
    } else {
        // 👤 BOUTON BLANC "AFFECTÉES À MOI" ACTIF À L'ÉCRAN
        // Règle d'or : Ma charge de travail personnelle.
        $sql .= " AND ((t.utilisateur_id = ? AND t.attribue_a IS NULL) OR t.attribue_a = ?)";
        $params[] = $id_session_user;
        $params[] = $id_session_user;
    }

    // 🛡️ SÉCURITÉ DE CLOISONNEMENT CENTRALISÉE PAR GROUPE (S'applique aux deux modes)
    if ($role !== 'admin') {
        $sql .= " AND (pr.utilisateur_id = ? OR EXISTS (
            SELECT 1 FROM todo_groupe_membres gm 
            WHERE gm.groupe_id = pr.groupe_id AND gm.utilisateur_id = ?
        ))";
        $params[] = $id_session_user;
        $params[] = $id_session_user;
    }

    // Application des filtres de catégories
    if (!empty($categories_id) && is_array($categories_id)) {
        $categories_nettoyees = array_map('intval', $categories_id);
        $points_interrogation = implode(',', array_fill(0, count($categories_nettoyees), '?'));
        $sql .= " AND tc.categorie_id IN ($points_interrogation)";
        foreach ($categories_nettoyees as $cat_id) { $params[] = $cat_id; }
    }
    
    // Barre de recherche textuelle
    if (!empty($recherche_texte)) {
        $sql .= " AND t.texte LIKE ?";
        $params[] = '%' . $recherche_texte . '%';
    }
    
    $sql .= " GROUP BY t.id ";
    $sql .= " ORDER BY t.statut ASC, t.id DESC";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 2. Récupère les données d'une seule tâche pour modification
 */
function getTachePourModification($lien, $id_tache) {
    $id_session_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    $sql = "SELECT t.id, t.projet_id, t.texte, t.statut, t.date_echeance, t.attribue_a 
            FROM todo_taches t
            INNER JOIN todo_projets pr ON t.projet_id = pr.id
            WHERE t.id = ?";
            
    $params = [(int)$id_tache];
    
    if ($role !== 'admin') {
        $sql .= " AND (t.utilisateur_id = ? OR t.attribue_a = ? OR pr.utilisateur_id = ?)";
        $params[] = $id_session_user;
        $params[] = $id_session_user;
        $params[] = $id_session_user;
    }
    
    $sql .= " LIMIT 1";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute($params);
    $donnees = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($donnees) {
        $sql_cats = "SELECT categorie_id FROM todo_tache_categories WHERE tache_id = ?";
        $stmt_cats = $lien->prepare($sql_cats);
        $stmt_cats->execute([(int)$id_tache]);
        $donnees['categories_id'] = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
    }
    
    return $donnees ? $donnees : null;
}
