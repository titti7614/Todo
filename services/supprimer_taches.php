<?php
// services/supprimer_taches.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML

// Récupération sécurisée en POST suite à la validation sur l'écran d'avertissement rouge
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache  = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;

if ($id_tache > 0 && $projet_id > 0) {
    // Suppression physique sécurisée par requête préparée PDO
    $sql_delete = "DELETE FROM todo_taches WHERE id = ? AND projet_id = ?";
    
    try {
        $stmt = $lien->prepare($sql_delete);
        $stmt->execute([$id_tache, $projet_id]);
        
        $_SESSION['succes_projet'] = "La tâche a été supprimée avec succès !";
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique lors de la suppression de la tâche : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Données insuffisantes pour supprimer la tâche.";
}

// Redirection propre vers le routeur racine local
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
