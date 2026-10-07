<?php
// services/ajouter_categories.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML

$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$nom_categories = isset($_POST['nom_categories']) ? trim($_POST['nom_categories']) : '';
$couleur_categories = isset($_POST['couleur_categories']) ? trim($_POST['couleur_categories']) : '#e67e22';

if ($projet_id > 0 && !empty($nom_categories)) {
    // 🎯 REQUÊTE HARMOMISÉE PDO : Utilisation de 'nom_categorie' en requêtes préparées sécurisées
    $sql = "INSERT INTO todo_categories (projet_id, nom_categorie, couleur) VALUES (?, ?, ?)";
    
    try {
        $stmt = $lien->prepare($sql);
        $stmt->execute([$projet_id, $nom_categories, $couleur_categories]);
        
        $_SESSION['succes_projet'] = "La catégorie '" . htmlspecialchars($nom_categories) . "' a été créée avec succès !";
    } catch (PDOException $e) {
        // Interception du code d'erreur de contrainte d'unicité (Duplicate entry)
        if ($e->errorInfo[1] === 1062) {
            $_SESSION['succes_projet'] = "La catégorie '" . htmlspecialchars($nom_categories) . "' est déjà configurée pour ce projet.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique SQL (Code " . $e->getCode() . ") : " . $e->getMessage();
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données incomplètes : le nom de la catégorie est obligatoire.";
}

// Redirection propre vers le routeur central
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
