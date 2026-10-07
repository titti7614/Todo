<?php
// services/sauvegarder_note.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML ici

// Récupération sécurisée des données envoyées par le formulaire de l'IHM
$projet_id  = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache   = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$note_texte = isset($_POST['note_texte']) ? trim($_POST['note_texte']) : '';

if ($id_tache > 0) {
    // Requête de mise à jour de la note
    $sql = "UPDATE todo_taches SET note = ? WHERE id = ?";
    
    try {
        $stmt = $lien->prepare($sql);
        $stmt->execute([$note_texte, $id_tache]);
        
        // 🎯 AJUSTEMENT INTELLIGENT DU MESSAGE DE SUCCÈS
        if (empty($note_texte)) {
            $_SESSION['succes_projet'] = "La note a été effacée avec succès.";
        } else {
            $_SESSION['succes_projet'] = "Note enregistrée avec succès !";
        }
        
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique SQL lors de la sauvegarde : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Identifiant de tâche invalide.";
}

// Redirection propre vers le routeur racine
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
