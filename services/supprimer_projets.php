<?php
// services/supprimer_projets.php - Version V3 PDO (To-Do - FIX Droit Admin Global)

if (isset($_POST['confirmer_suppression']) && $projet_id > 0) {
    // SÉCURITÉ MULTI-USER : On intercepte l'ID de l'utilisateur connecté et son rôle
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    $role_applicatif = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    try {
        $autoriser_suppression = false;

        // 1. AIGUILLAGE ET CONTRÔLE DES PASS DROITS
        if ($role_applicatif === 'admin') {
            // 👑 Le Super-Admin bypass la vérification de propriété et peut cibler n'importe quel projet
            $query_check = "SELECT id FROM todo_projets WHERE id = ? LIMIT 1";
            $stmt_check = $lien->prepare($query_check);
            $stmt_check->execute([$projet_id]);
            if ($stmt_check->fetch()) {
                $autoriser_suppression = true;
            }
        } else {
            // 👤 L'utilisateur standard reste strictement soumis au double verrou
            $query_check = "SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1";
            $stmt_check = $lien->prepare($query_check);
            $stmt_check->execute([$projet_id, $utilisateur_id]);
            if ($stmt_check->fetch()) {
                $autoriser_suppression = true;
            }
        }
        
        // 2. EXÉCUTION DE LA SUPPRESSION EN CASCADE ADAPTATIVE
        if ($autoriser_suppression) {
            
            if ($role_applicatif === 'admin') {
                // L'admin nettoie absolument TOUTES les tâches du projet, peu importe leur créateur
                $stmt_taches = $lien->prepare("DELETE FROM todo_taches WHERE projet_id = ?");
                $stmt_taches->execute([$projet_id]);
                
                // Suppression définitive du projet global
                $stmt_projet = $lien->prepare("DELETE FROM todo_projets WHERE id = ?");
                $stmt_projet->execute([$projet_id]);
            } else {
                // L'user standard ne nettoie que ses propres tâches et son projet
                $stmt_taches = $lien->prepare("DELETE FROM todo_taches WHERE projet_id = ? AND utilisateur_id = ?");
                $stmt_taches->execute([$projet_id, $utilisateur_id]);
                
                $stmt_projet = $lien->prepare("DELETE FROM todo_projets WHERE id = ? AND utilisateur_id = ?");
                $stmt_projet->execute([$projet_id, $utilisateur_id]);
            }
            
            // Les catégories sont liées uniquement au projet, elles sautent dans tous les cas
            $stmt_categories = $lien->prepare("DELETE FROM todo_categories WHERE projet_id = ?");
            $stmt_categories->execute([$projet_id]);
            
            $_SESSION['succes_projet'] = "Le projet ainsi que toutes ses catégories et tâches ont été supprimés avec succès.";
        } else {
            $_SESSION['erreur_projet'] = "Action non autorisée : ce projet ne vous appartient pas.";
        }
        
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
