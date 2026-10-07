<?php
// services/ajouter_categories.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML

$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$nom_categories = isset($_POST['nom_categories']) ? trim($_POST['nom_categories']) : '';
$couleur_categories = isset($_POST['couleur_categories']) ? trim($_POST['couleur_categories']) : '#e67e22';

// Récupération de la variable globale initialisée par l'index sécurisé
$utilisateur_id = $_SESSION['user_id'] ?? 1;

if ($projet_id > 0 && !empty($nom_categories)) {
    
    // VERIFICATION DE SÉCURITÉ NIVEAU 2 : Le projet appartient-il bien à l'utilisateur connecté ?
    $stmt_verif = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1");
    $stmt_verif->execute([$projet_id, $utilisateur_id]);
    
    if ($stmt_verif->fetch()) {
        // Le projet est valide, insertion sécurisée de la catégorie
        $sql = "INSERT INTO todo_categories (projet_id, nom_categorie, couleur) VALUES (?, ?, ?)";
        
        try {
            $stmt = $lien->prepare($sql);
            $stmt->execute([$projet_id, $nom_categories, $couleur_categories]);
            
            $_SESSION['succes_projet'] = "La catégorie '" . htmlspecialchars($nom_categories) . "' a été créée avec succès !";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                $_SESSION['succes_projet'] = "La catégorie '" . htmlspecialchars($nom_categories) . "' est déjà configurée pour ce projet.";
            } else {
                $_SESSION['erreur_projet'] = "Erreur technique SQL (Code " . $e->getCode() . ") : " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['erreur_projet'] = "Accès refusé : Action non autorisée sur ce projet.";
    }
} else {
    $_SESSION['erreur_projet'] = "Données incomplètes : le nom de la catégorie est obligatoire.";
}

// Redirection relative adaptative pour la Prod o2switch
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
