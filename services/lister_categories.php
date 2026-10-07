<?php
// services/lister_categories.php - Version V3 PDO (To-Do)

/**
 * 1. Récupère toutes les catégories uniques rattachées à un projet
 */
function getcategoriesParProjet($lien, $projet_id) {
    // Requête préparée PDO sur la table mise à jour
    $sql = "SELECT id, nom_categorie FROM todo_categories WHERE projet_id = ? ORDER BY id ASC";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$projet_id]);
    
    // fetchAll() récupère toutes les lignes d'un coup sous forme de tableau associatif
    return $stmt->fetchAll();
}

/**
 * 2. 🎯 Récupère les données d'une seule catégorie pour alimenter l'IHM lors des modifications
 */
function getcategoriesPourModification($lien, $categories_id) {
    // Aligné sur les colonnes réelles : id, nom_categorie, projet_id
    $sql = "SELECT id, nom_categorie, projet_id FROM todo_categories WHERE id = ? LIMIT 1";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$categories_id]);
    $donnees = $stmt->fetch(); // On récupère une seule ligne
    
    return $donnees ? $donnees : null;
}
?>
