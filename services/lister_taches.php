<?php
// services/lister_taches.php - Version V3 PDO Multi-User (Double Verrou + Tri Urgence Absolu + Multi-Catégories)

/**
 * 1. Récupère les tâches d'un projet avec double filtrage, tri intelligent ET cloisonnement (Multi-Catégories)
 */
function getTachesParProjet($lien, $projet_id, $categories_id = [], $recherche_texte = '') {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    // 🎯 Modification : GROUP_CONCAT pour concaténer les informations des multi-catégories
    $sql = "SELECT t.id, t.texte, t.statut, t.note, t.date_echeance, 
                   GROUP_CONCAT(c.nom_categorie SEPARATOR '|||') AS nom_categories, 
                   GROUP_CONCAT(c.couleur SEPARATOR '|||') AS couleur_categories 
            FROM todo_taches t
            INNER JOIN todo_projets pr ON t.projet_id = pr.id
            -- 🎯 Jointures via la nouvelle table pivot harmonisée
            LEFT JOIN todo_tache_categories tc ON t.id = tc.tache_id
            LEFT JOIN todo_categories c ON tc.categorie_id = c.id
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
        
        // 🎯 Filtrage adapté : on vérifie la présence dans la table pivot
        $sql .= " AND tc.categorie_id IN ($points_interrogation)";
        foreach ($categories_nettoyees as $cat_id) { $params[] = $cat_id; }
    }
    
    // Barre de recherche textuelle
    if (!empty($recherche_texte)) {
        $sql .= " AND t.texte LIKE ?";
        $params[] = '%' . $recherche_texte . '%';
    }
    
    // 🎯 Obligatoire avec GROUP_CONCAT pour ne pas fusionner toutes les tâches entre elles
    $sql .= " GROUP BY t.id ";

    // 🎯 REQUÊTE DE TRI FORCE MAJEUR CONSERVÉE
    $sql .= " ORDER BY 
                t.statut ASC, 
                CASE 
                    WHEN t.date_echeance < CURDATE() AND t.statut != 2 THEN 1  -- En retard ⚠️
                    WHEN t.date_echeance = CURDATE() AND t.statut != 2 THEN 2  -- Aujourd'hui 📅
                    WHEN t.date_echeance > CURDATE() AND t.statut != 2 THEN 3  -- Futur
                    ELSE 4                                                     -- Sans date / Terminé
                END ASC, 
                t.date_echeance ASC, 
                t.id DESC";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 2. Récupère les données d'une seule tâche pour modification (Avec tableau d'IDs de catégories)
 */
function getTachePourModification($lien, $id_tache) {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    // Extraction des informations de base de la tâche
    $sql = "SELECT t.id, t.projet_id, t.texte, t.statut, t.date_echeance 
            FROM todo_taches t
            INNER JOIN todo_projets pr ON t.projet_id = pr.id
            WHERE t.id = ?";
            
    $params = [(int)$id_tache];
    
    if ($role !== 'admin') {
        $sql .= " AND (t.utilisateur_id = ? OR t.user_id = ? OR pr.utilisateur_id = ?)";
        $params[] = $utilisateur_id;
        $params[] = $utilisateur_id;
        $params[] = $utilisateur_id;
    }
    
    $sql .= " LIMIT 1";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute($params);
    $donnees = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($donnees) {
        // 🎯 On va chercher tous les IDs des catégories liées à cette tâche
        $sql_cats = "SELECT categorie_id FROM todo_tache_categories WHERE tache_id = ?";
        $stmt_cats = $lien->prepare($sql_cats);
        $stmt_cats->execute([(int)$id_tache]);
        
        // On stocke ces IDs sous forme de tableau indexé simple (ex:) dans l'index 'categories_id'
        $donnees['categories_id'] = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
    }
    
    return $donnees ? $donnees : null;
}
?>
