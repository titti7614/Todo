<?php
// services/supprimer_taches.php
// Script logique pure - Aucun code HTML ici

// 1. Récupération de l'ID de la tâche à supprimer transmis par l'URL (GET)
$id_tache = isset($_GET['id_tache']) ? (int)$_GET['id_tache'] : 0;
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;

if ($id_tache > 0) {
    // 🎯 CORRECTIF : Déclaration explicite et sécurisée de la variable de requête
    $sql_delete = "DELETE FROM todo_taches WHERE id = $id_tache";
    
    if (mysqli_query($lien, $sql_delete)) {
        $_SESSION['succes_projet'] = "La tâche a été supprimée avec succès.";
    } else {
        $_SESSION['erreur_projet'] = "Erreur technique lors de la suppression de la tâche : " . mysqli_error($lien);
    }
} else {
    $_SESSION['erreur_projet'] = "Identifiant de tâche invalide.";
}

// 2. Redirection propre vers la liste du projet actif
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
