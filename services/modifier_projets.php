<?php
// services/modifier_projets.php — Version V4.0.0 Collaborative Pure MVC (Livraison Officielle)

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// 🛡️ Barrière de sécurité de session globale
if (!isset($_SESSION['user_id']) || !isset($lien)) {
    header("Location: index.php");
    exit();
}

$id_session_user = (int)$_SESSION['user_id'];
$projet_id  = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$nom_projet = isset($_POST['nom_projet']) ? trim($_POST['nom_projet']) : '';

// 🎯 Interception adaptative du changement de cercle d'équipe
$groupe_id  = (!empty($_POST['groupe_id_selectionne']) && (int)$_POST['groupe_id_selectionne'] > 0) ? (int)$_POST['groupe_id_selectionne'] : null;

if ($projet_id > 0 && !empty($nom_projet)) {
    try {
        // Sécurité : On s'assure que l'utilisateur est bien le propriétaire légitime du projet pour le modifier
        $stmt_check = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1");
        $stmt_check->execute([$projet_id, $id_session_user]);
        
        if ($stmt_check->fetch() || ($_SESSION['mes_apps_cache']['todo'] ?? 'user') === 'admin') {
            
            // 1. UPDATE COLLABORATIF : Mise à jour du nom du projet et du jeton de groupe associé
            $sql = "UPDATE todo_projets SET nom_projet = ?, groupe_id = ? WHERE id = ?";
            $stmt = $lien->prepare($sql);
            $stmt->execute([$nom_projet, $groupe_id, $projet_id]);
            
            // 2. 🧹 NETTOYAGE ERGONOMIQUE V4.0.0 : Si le projet redevient STRICTEMENT PRIVÉ (groupe_id est null)
            // On désassigne automatiquement toutes les tâches pour qu'elles redeviennent vos tâches privées solos,
            // et on purge l'historique des notifications pour ne pas laisser de lignes orphelines.
            if ($groupe_id === null) {
                // Les tâches d'équipe perdent leur assignation et redeviennent la propriété exclusive du créateur
                $stmt_clean_taches = $lien->prepare("UPDATE todo_taches SET attribue_a = NULL WHERE projet_id = ?");
                $stmt_clean_taches->execute([$projet_id]);

                // On supprime les anciens verrous d'acquittement de relecture du Point X des collègues
                $stmt_clean_notifs = $lien->prepare("DELETE FROM todo_notifications_lectures WHERE projet_id = ?");
                $stmt_clean_notifs->execute([$projet_id]);
            }
            
            $_SESSION['succes_projet'] = "Le projet a été mis à jour et son périmètre de partage a été modifié avec succès.";
        } else {
            $_SESSION['erreur_projet'] = "Accès refusé : Vous n'êtes pas le propriétaire de ce projet.";
        }
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique SQL lors de la modification : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Veuillez renseigner un nom de projet valide.";
}

// Redirection standard de l'architecture MVC vers la liste du projet actif
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
