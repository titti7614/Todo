<?php
// services/modifier_categories.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML

$projet_id     = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$categories_id      = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;
$nom_categories     = isset($_POST['nom_categories']) ? trim($_POST['nom_categories']) : '';
$couleur_categories = isset($_POST['couleur_categories']) ? trim($_POST['couleur_categories']) : '#e67e22';

$utilisateur_id = $_SESSION['user_id'] ?? 1;

if ($categories_id > 0 && $projet_id > 0 && !empty($nom_categories)) {
    
    // VERIFICATION ET CLOISONNEMENT NIVEAU 2 : On s'assure que le projet lié appartient bien à l'utilisateur
    $sql_update = "UPDATE todo_categories c
                   INNER JOIN todo_projets p ON c.projet_id = p.id
                   SET c.nom_categorie = ?, c.couleur = ? 
                   WHERE c.id = ? AND c.projet_id = ? AND p.utilisateur_id = ?"; 
                   
    try {
        $stmt = $lien->prepare($sql_update);
        $stmt->execute([$nom_categories, $couleur_categories, $categories_id, $projet_id, $utilisateur_id]);
        
        $_SESSION['succes_projet'] = "La catégorie a été modifiée avec succès !";
    } catch (PDOException $e) {
        if ($e->errorInfo === 1062) {
            $_SESSION['erreur_projet'] = "Erreur : La catégorie '" . htmlspecialchars($nom_categories) . "' existe déjà pour ce projet.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique de base de données : " . $e->getMessage();
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données du formulaire invalides ou incomplètes.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
