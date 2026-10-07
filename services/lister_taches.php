<?php
// services/lister_taches.php - Version V3 PDO (To-Do)

/**
 * 1. Récupère les tâches d'un projet avec double filtrage, tri intelligent ET chargement du champ note
 */
function getTachesParProjet($lien, $projet_id, $categories_id = [], $recherche_texte = '') {
    // Tableau pour stocker les paramètres sécurisés à exécuter dans PDO
    $params = [(int)$projet_id];
    
    // 🎯 REQUÊTE UNIFIÉE v3.0 : Alignée sur la base de données propre (nom_categorie, couleur_categorie, categorie_id)
    $sql = "SELECT t.id, t.texte, t.statut, t.note, p.nom_categorie AS nom_categories, p.couleur AS couleur_categories 
            FROM todo_taches t
            LEFT JOIN todo_categories p ON t.categorie_id = p.id
            WHERE t.projet_id = ?";
            
    // FILTRE 1 : Multi-catégories (Cases à cocher)
    if (!empty($categories_id) && is_array($categories_id)) {
        $categories_nettoyees = array_map('intval', $categories_id);
        $points_interrogation = implode(',', array_fill(0, count($categories_nettoyees), '?'));
        
        $sql .= " AND t.categorie_id IN ($points_interrogation)";
        
        foreach ($categories_nettoyees as $cat_id) {
            $params[] = $cat_id;
        }
    }
    
    // FILTRE 2 : Barre de recherche textuelle
    if (!empty($recherche_texte)) {
        $sql .= " AND t.texte LIKE ?";
        $params[] = '%' . $recherche_texte . '%';
    }
    
    // TRI INTELLIGENT v3.0 : En cours d'abord, puis par ID décroissant
    $sql .= " ORDER BY t.statut ASC, t.id DESC";
            
    $stmt = $lien->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * 2. Récupère les données d'une seule tâche alignées sur la nouvelle colonne (categorie_id)
 */
function getTachePourModification($lien, $id_tache) {
    // 🎯 HARMONISATION : Utilisation de la colonne propre 'categorie_id'
    $sql = "SELECT id, projet_id, categorie_id AS categories_id, texte, statut FROM todo_taches WHERE id = ? LIMIT 1";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$id_tache]);
    $donnees = $stmt->fetch();
    
    return $donnees ? $donnees : null;
}
?>
