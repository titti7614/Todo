<?php
// services/modifier_categories.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML

$projet_id     = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$categories_id      = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;
$nom_categories     = isset($_POST['nom_categories']) ? trim($_POST['nom_categories']) : '';
$couleur_categories = isset($_POST['couleur_categories']) ? trim($_POST['couleur_categories']) : '#e67e22';

if ($categories_id > 0 && $projet_id > 0 && !empty($nom_categories)) {
    
    // 🎯 REQUÊTE SÉCURISÉE ET HARMONISÉE PDO : Utilisation de nom_categorie en requêtes préparées
    $sql_update = "UPDATE todo_categories 
                   SET nom_categorie = ?, couleur = ? 
                   WHERE id = ? AND projet_id = ?"; 
                   
    try {
        $stmt = $lien->prepare($sql_update);
        $stmt->execute([$nom_categories, $couleur_categories, $categories_id, $projet_id]);
        
        $_SESSION['succes_projet'] = "La catégorie a été modifiée avec succès !";
    } catch (PDOException $e) {
        // Capture de l'erreur de doublon sur la contrainte d'unicité (code 1062)
        if ($e->errorInfo === 1062) {
            $_SESSION['erreur_projet'] = "Erreur : La catégorie '" . htmlspecialchars($nom_categories) . "' existe déjà pour ce projet.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique de base de données : " . $e->getMessage();
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données du formulaire invalides ou incomplètes.";
}

// Redirection alignée sur la racine locale
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
