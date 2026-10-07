<?php
// services/modifier_projets.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML ici

// 1. Récupération des données postées par le formulaire de renommage
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$nouveau_nom = isset($_POST['nouveau_nom_projet']) ? trim($_POST['nouveau_nom_projet']) : '';

if ($projet_id > 0 && !empty($nouveau_nom)) {
    // SÉCURITÉ MULTI-USER : On récupère l'ID de l'utilisateur connecté via la session
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    // 2. Requête SQL préparée avec double verrou : ID du projet ET ID de l'utilisateur
    $sql = "UPDATE todo_projets SET nom_projet = ? WHERE id = ? AND utilisateur_id = ?";
    
    try {
        $stmt = $lien->prepare($sql);
        $stmt->execute([$nouveau_nom, $projet_id, $utilisateur_id]);
        
        $_SESSION['succes_projet'] = "Le projet a été renommé en '" . htmlspecialchars($nouveau_nom) . "' avec succès !";
    } catch (PDOException $e) {
        // Gestion des cas de doublon (contrainte UNIQUE) isolée par utilisateur
        if ($e->errorInfo === 1062) {
            $_SESSION['erreur_projet'] = "Un projet porte déjà le nom '" . htmlspecialchars($nouveau_nom) . "'.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique de base de données : " . $e->getMessage();
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données incomplètes pour modifier le projet.";
}

// 3. Redirection propre vers la liste du projet modifié
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
