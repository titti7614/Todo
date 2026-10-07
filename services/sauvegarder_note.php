<?php
// services/sauvegarder_note.php - Version V3 PDO Multi-User (Double Verrou Sécurité)
// Script logique pure - Aucun code HTML ici

$projet_id  = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache   = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$note_texte = isset($_POST['note_texte']) ? trim($_POST['note_texte']) : '';

$utilisateur_id = $_SESSION['user_id'] ?? 1;
$role           = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

if ($id_tache > 0) {
    
    // Détermination de la requête selon les privilèges
    if ($role === 'admin') {
        // L'admin a les pleins pouvoirs de modification sur toutes les notes du projet
        $sql = "UPDATE todo_taches SET note = ? WHERE id = ?";
        $params = [$note_texte, $id_tache];
    } else {
        // L'user standard est contraint par son identifiant sur les deux champs possibles
        $sql = "UPDATE todo_taches 
                SET note = ? 
                WHERE id = ? AND (utilisateur_id = ? OR user_id = ?)";
        $params = [$note_texte, $id_tache, $utilisateur_id, $utilisateur_id];
    }
    
    try {
        $stmt = $lien->prepare($sql);
        $stmt->execute($params);
        
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

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
