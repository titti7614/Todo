<?php
// services/supprimer_taches.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML

$projet_id      = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache       = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$utilisateur_id = $_SESSION['user_id'] ?? 1;
$role           = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

if ($id_tache > 0 && $projet_id > 0) {
    if ($role === 'admin') {
        // L'admin supprime n'importe quelle tâche du projet
        $sql = "DELETE FROM todo_taches WHERE id = ? AND projet_id = ?";
        $params = [$id_tache, $projet_id];
    } else {
        // L'user ne peut supprimer que sa tâche
        $sql = "DELETE FROM todo_taches WHERE id = ? AND projet_id = ? AND utilisateur_id = ?";
        $params = [$id_tache, $projet_id, $utilisateur_id];
    }
    
    try {
        $stmt = $lien->prepare($sql);
        $stmt->execute($params);
        
        // Confirmation sémantique pour le bandeau vert
        $_SESSION['succes_projet'] = "La tâche a été supprimée avec succès !";
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique lors de la suppression : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Données insuffisantes pour supprimer la tâche.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
