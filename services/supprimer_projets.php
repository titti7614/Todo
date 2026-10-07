<?php
// services/supprimer_projets.php - Version V3 PDO (To-Do)

if (isset($_POST['confirmer_suppression']) && $projet_id > 0) {
    // SÉCURITÉ MULTI-USER : On intercepte l'ID de l'utilisateur connecté
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    try {
        // 1. Double vérification de propriété : on s'assure d'abord que le projet appartient bien à l'utilisateur
        $query_check = "SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1";
        $stmt_check = $lien->prepare($query_check);
        $stmt_check->execute([$projet_id, $utilisateur_id]);
        
        if ($stmt_check->fetch()) {
            // L'utilisateur est bien le propriétaire : on lance la suppression en cascade sécurisée
            
            // Suppression des tâches liées au projet et à cet utilisateur spécifique
            $stmt_taches = $lien->prepare("DELETE FROM todo_taches WHERE projet_id = ? AND utilisateur_id = ?");
            $stmt_taches->execute([$projet_id, $utilisateur_id]);
            
            // Suppression des catégories liées au projet
            $stmt_categories = $lien->prepare("DELETE FROM todo_categories WHERE projet_id = ?");
            $stmt_categories->execute([$projet_id]);
            
            // Suppression définitive du projet
            $stmt_projet = $lien->prepare("DELETE FROM todo_projets WHERE id = ? AND utilisateur_id = ?");
            $stmt_projet->execute([$projet_id, $utilisateur_id]);
            
            $_SESSION['succes_projet'] = "Le projet ainsi que toutes ses catégories et tâches ont été supprimés.";
        } else {
            $_SESSION['erreur_projet'] = "Action non autorisée : ce projet ne vous appartient pas.";
        }
        
        // Redirection relative adaptative
        header("Location: index.php?action=liste");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique SQL lors de la suppression : " . $e->getMessage();
        header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
        exit();
    }
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
