<?php
// services/lister_categories.php - Version V3 PDO (To-Do - FIX Pastilles Couleurs)

/**
 * 1. Récupère toutes les catégories uniques rattachées à un projet (Avec couleur)
 */
function getcategoriesParProjet($lien, $projet_id) {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    // 🎯 FIX : Ajout de c.couleur dans le SELECT pour alimenter l'IHM
    $sql = "SELECT c.id, c.nom_categorie, c.couleur 
            FROM todo_categories c
            INNER JOIN todo_projets p ON c.projet_id = p.id
            WHERE c.projet_id = ? AND p.utilisateur_id = ? 
            ORDER BY c.id ASC";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$projet_id, $utilisateur_id]);
    
    return $stmt->fetchAll();
}

/**
 * 2. Récupère les données d'une seule catégorie pour modification (Avec couleur)
 */
function getcategoriesPourModification($lien, $categories_id) {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    // 🎯 FIX : Ajout de c.couleur ici aussi pour pré-remplir le quadrillage lors de l'édition
    $sql = "SELECT c.id, c.nom_categorie, c.couleur, c.projet_id 
            FROM todo_categories c
            INNER JOIN todo_projets p ON c.projet_id = p.id
            WHERE c.id = ? AND p.utilisateur_id = ? 
            LIMIT 1";
    
    $stmt = $lien->prepare($sql);
    $stmt->execute([(int)$categories_id, $utilisateur_id]);
    $donnees = $stmt->fetch();
    
    return $donnees ? $donnees : null;
}
?>
