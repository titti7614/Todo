<?php
// services/supprimer_projets.php - Version V4.1.1 Collaborative Stable

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || !isset($lien)) {
    header("Location: index.php");
    exit();
}

$id_session_user = (int)$_SESSION['user_id'];
$role_applicatif = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

// On traite la suppression car l'action est confirmée par le formulaire
if ($projet_id > 0) {
    try {
        $autoriser_suppression = false;

        if ($role_applicatif === 'admin') {
            $autoriser_suppression = true;
        } else {
            $query_check = "SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1";
            $stmt_check = $lien->prepare($query_check);
            $stmt_check->execute([$projet_id, $id_session_user]);
            if ($stmt_check->fetch()) { $autoriser_suppression = true; }
        }
        
        if ($autoriser_suppression) {
            // Nettoyage en cascade complet
            $stmt_notifs = $lien->prepare("DELETE FROM todo_notifications_lectures WHERE projet_id = ?");
            $stmt_notifs->execute([$projet_id]);

            $stmt_taches = $lien->prepare("DELETE FROM todo_taches WHERE projet_id = ?");
            $stmt_taches->execute([$projet_id]);
            
            $stmt_categories = $lien->prepare("DELETE FROM todo_categories WHERE projet_id = ?");
            $stmt_categories->execute([$projet_id]);

            $stmt_projet = $lien->prepare("DELETE FROM todo_projets WHERE id = ?");
            $stmt_projet->execute([$projet_id]);
            
            $_SESSION['succes_projet'] = "Le projet collaboratif a été supprimé avec succès.";
            header("Location: index.php?projet_id=0&action=liste");
            exit();
        } else {
            $_SESSION['erreur_projet'] = "Action refusée : Droits insuffisants.";
        }
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur SQL : " . $e->getMessage();
    }
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
