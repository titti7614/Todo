<?php
// services/supprimer_phases.php

// Récupération sécurisée en POST suite à la validation de l'écran d'avertissement graphique
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$phase_id  = isset($_POST['phase_id']) ? (int)$_POST['phase_id'] : 0;

// (Laissez le reste du script SQL intact en dessous...)


if ($phase_id > 0 && $projet_id > 0) {
    // 1. Sécurité : On détache les tâches liées à cette phase pour qu'elles passent en "Général" (0) au lieu d'être perdues
    $sql_detach = "UPDATE todo_taches SET phase_id = 0 WHERE phase_id = $phase_id AND projet_id = $projet_id";
    mysqli_query($lien, $sql_detach);

    // 2. Suppression de la phase
    $sql_delete = "DELETE FROM todo_phases WHERE id = $phase_id AND projet_id = $projet_id";
    
    if (mysqli_query($lien, $sql_delete)) {
        $_SESSION['succes_projet'] = "La phase a été supprimée avec succès !";
    } else {
        $_SESSION['erreur_projet'] = "Erreur lors de la suppression de la phase.";
    }
} else {
    $_SESSION['erreur_projet'] = "Données manquantes pour supprimer la phase.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
