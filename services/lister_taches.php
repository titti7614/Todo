<?php
// services/lister_taches.php - Version V3 PDO Multi-User (Double Verrou Champs Fix)

/**
 * 1. Récupère les tâches d'un projet avec double filtrage, tri intelligent ET cloisonnement
 */
function getTachesParProjet($lien, $projet_id, $categories_id = [], $recherche_texte = '') {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    // Le verrou WHERE impose t.utilisateur_id = ? ou pr.utilisateur_id = ? selon le rôle
    $sql = "SELECT t.id, t.texte, t.statut, t.note, c.nom_categorie AS nom_categories, c.couleur AS couleur_categories 
            FROM todo_taches t
            LEFT JOIN todo_categories c ON t.categorie_id = c.id
            INNER JOIN todo_projets pr ON t.projet_id = pr.id
            WHERE t.projet_id = ?";
            
    $params = [(int)$projet_id];
    
    // Si USER simple : Sécurité subtile Niveau 2 (Il ne voit que ses créations)
    if ($role !== 'admin') {
        $sql .= " AND (t.utilisateur_id = ? OR t.user_id = ? OR pr.utilisateur_id = ?)";
        $params[] = $utilisateur_id;
        $params[] = $utilisateur_id;
        $params[] = $utilisateur_id;
    }
    
    // Application des filtres (Multi-catégories)
    if (!empty($categories_id) && is_array($categories_id)) {
        $categories_nettoyees = array_map('intval', $categories_id);
        $points_interrogation = implode(',', array_fill(0, count($categories_nettoyees), '?'));
        $sql .= " AND t.categorie_id IN ($points_interrogation)";
        foreach ($categories_nettoyees as $cat_id) { $params[] = $cat_id; }
    }
    
    // Barre de recherche textuelle
    if (!empty($recherche_texte)) {
        $sql .= " AND t.texte LIKE ?";
        $params[] = '%' . $recherche_texte . '%';
    }
    
    $sql .= " ORDER BY t.statut ASC, t.id DESC";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * 2. 🎯 FIX CRITIQUE : Récupère les données d'une seule tâche pour modification
 */
function getTachePourModification($lien, $id_tache) {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    // Extraction directe sans blocage de jointure restrictive
    $sql = "SELECT t.id, t.projet_id, t.categorie_id AS categories_id, t.texte, t.statut 
            FROM todo_taches t
            INNER JOIN todo_projets pr ON t.projet_id = pr.id
            WHERE t.id = ?";
            
    $params = [(int)$id_tache];
    
    // Si USER standard : On s'assure qu'il est bien le propriétaire de la tâche ou du projet parent
    if ($role !== 'admin') {
        $sql .= " AND (t.utilisateur_id = ? OR t.user_id = ? OR pr.utilisateur_id = ?)";
        $params[] = $utilisateur_id;
        $params[] = $utilisateur_id;
        $params[] = $utilisateur_id;
    }
    
    $sql .= " LIMIT 1";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute($params);
    $donnees = $stmt->fetch();
    
    return $donnees ? $donnees : null;
}
?>
