<?php
// services/supprimer_taches.php
// Script logique pure - Aucun code HTML

// Récupération sécurisée en POST suite à la validation sur l'écran d'avertissement rouge
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache  = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;

if ($id_tache > 0 && $projet_id > 0) {
    // Suppression physique de la ligne dans la table todo_taches
    $sql_delete = "DELETE FROM todo_taches WHERE id = $id_tache AND projet_id = $projet_id";
    
    if (mysqli_query($lien, $sql_delete)) {
        $_SESSION['succes_projet'] = "La tâche a été supprimée avec succès !";
    } else {
        $_SESSION['erreur_projet'] = "Erreur lors de la suppression de la tâche.";
    }
} else {
    $_SESSION['erreur_projet'] = "Données insuffisantes pour supprimer la tâche.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
