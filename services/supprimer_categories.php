<?php
// services/supprimer_categories.php - Version V3 PDO (To-Do)

// Récupération sécurisée en POST suite à la validation de l'écran d'avertissement graphique
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$categories_id  = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;

$utilisateur_id = $_SESSION['user_id'] ?? 1;

if ($categories_id > 0 && $projet_id > 0) {
    try {
        // VERIFICATION DE SÉCURITÉ NIVEAU 2 : Le projet appartient-il bien à l'utilisateur connecté ?
        $stmt_verif = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1");
        $stmt_verif->execute([$projet_id, $utilisateur_id]);
        
        if ($stmt_verif->fetch()) {
            // 1. Sécurité : On détache les tâches liées à cette catégorie pour qu'elles passent en "Général" (0)
            // Le filtre utilisateur_id est appliqué pour garantir le cloisonnement
            $sql_detach = "UPDATE todo_taches SET categorie_id = 0 WHERE categorie_id = ? AND projet_id = ? AND utilisateur_id = ?";
            $stmt_detach = $lien->prepare($sql_detach);
            $stmt_detach->execute([$categories_id, $projet_id, $utilisateur_id]);

            // 2. Suppression définitive de la catégorie
            $sql_delete = "DELETE FROM todo_categories WHERE id = ? AND projet_id = ?";
            $stmt_delete = $lien->prepare($sql_delete);
            $stmt_delete->execute([$categories_id, $projet_id]);
            
            $_SESSION['succes_projet'] = "La catégorie a été supprimée avec succès (tâches conservées en 'Général') !";
        } else {
            $_SESSION['erreur_projet'] = "Accès refusé : Action non autorisée.";
        }
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique lors de la suppression de la catégorie : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Données manquantes pour supprimer la catégorie.";
}

// Redirection adaptative relative pour la Prod
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
