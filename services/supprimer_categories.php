<?php
// services/supprimer_categories.php - Version V3 PDO (To-Do)

// Récupération sécurisée en POST suite à la validation de l'écran d'avertissement graphique
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$categories_id  = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;

if ($categories_id > 0 && $projet_id > 0) {
    try {
        // 1. Sécurité : On détache les tâches liées à cette catégorie pour qu'elles passent en "Général" (0)
        // Aligné sur la colonne propre 'categorie_id' en requêtes préparées PDO
        $sql_detach = "UPDATE todo_taches SET categorie_id = 0 WHERE categorie_id = ? AND projet_id = ?";
        $stmt_detach = $lien->prepare($sql_detach);
        $stmt_detach->execute([$categories_id, $projet_id]);

        // 2. Suppression définitive de la catégorie
        $sql_delete = "DELETE FROM todo_categories WHERE id = ? AND projet_id = ?";
        $stmt_delete = $lien->prepare($sql_delete);
        $stmt_delete->execute([$categories_id, $projet_id]);
        
        $_SESSION['succes_projet'] = "La catégorie a été supprimée avec succès (tâches conservées en 'Général') !";
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique lors de la suppression de la catégorie : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Données manquantes pour supprimer la catégorie.";
}

// Redirection forcée vers l'index absolu pour vider l'IHM de la catégorie supprimée
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();

?>
