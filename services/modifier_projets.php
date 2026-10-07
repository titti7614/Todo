<?php
// services/modifier_projets.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML ici

$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$nouveau_nom = isset($_POST['nouveau_nom_projet']) ? trim($_POST['nouveau_nom_projet']) : '';

if ($projet_id > 0 && !empty($nouveau_nom)) {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    $sql = "UPDATE todo_projets SET nom_projet = ? WHERE id = ? AND utilisateur_id = ?";
    
    try {
        $stmt = $lien->prepare($sql);
        $stmt->execute([$nouveau_nom, $projet_id, $utilisateur_id]);
        
        $_SESSION['succes_projet'] = "Le projet a été renommé en '" . htmlspecialchars($nouveau_nom) . "' avec succès !";
    } catch (PDOException $e) {
        if ($e->errorInfo === 1062) {
            $_SESSION['erreur_projet'] = "Un projet porte déjà le nom '" . htmlspecialchars($nouveau_nom) . "'.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique de base de données : " . $e->getMessage();
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données incomplètes pour modifier le projet.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
