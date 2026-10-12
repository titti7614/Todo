<?php
// services/modifier_taches.php - Version V4.0.0 Collaborative Pure MVC (Livraison Officielle)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛡️ Barrière de sécurité de session globale
if (!isset($_SESSION['user_id']) || !isset($lien)) {
    header("Location: index.php");
    exit();
}

$tache_id               = isset($_POST['tache_id']) ? (int)$_POST['tache_id'] : 0;
$projet_id              = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$texte_tache            = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$date_echeance          = (!empty($_POST['date_echeance'])) ? trim($_POST['date_echeance']) : null;
$statut                 = isset($_POST['statut']) ? (int)$_POST['statut'] : 0;

// 🎯 Nouveauté V4.0.0 : Récupération sécurisée du collaborateur assigné
$attribue_a             = (!empty($_POST['attribue_a']) && (int)$_POST['attribue_a'] > 0) ? (int)$_POST['attribue_a'] : null;

// Interception adaptative multi-catégories (avec ou sans S)
$categories_ids = [];
if (isset($_POST['categories_ids']) && is_array($_POST['categories_ids'])) {
    $categories_ids = $_POST['categories_ids'];
} elseif (isset($_POST['categories_id']) && is_array($_POST['categories_id'])) {
    $categories_id = $_POST['categories_id'];
}

$id_session_user = (int)$_SESSION['user_id'];
$role            = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

if ($tache_id > 0 && !empty($texte_tache)) {
    
    // ======================================================================
    // 🧠 VÉRIFICATION DES DROITS DE MODIFICATION COLLABORATIFS V4.0.0
    // ======================================================================
    $autorise = false;
    
    if ($role === 'admin') {
        $stmt_verif = $lien->prepare("SELECT id FROM todo_taches WHERE id = ? LIMIT 1");
        $stmt_verif->execute([$tache_id]);
        if ($stmt_verif->fetch()) { $autorise = true; }
    } else {
        // Un utilisateur a le droit de modifier une tâche s'il en est l'auteur, 
        // s'il en est l'actuel assigné, ou s'il appartient au cercle du projet associé
        $stmt_verif = $lien->prepare("
            SELECT t.id FROM todo_taches t
            INNER JOIN todo_projets pr ON t.projet_id = pr.id
            LEFT JOIN todo_groupe_membres gm ON pr.groupe_id = gm.groupe_id
            WHERE t.id = ? AND (t.utilisateur_id = ? OR t.attribue_a = ? OR pr.utilisateur_id = ? OR gm.utilisateur_id = ?)
            LIMIT 1
        ");
        $stmt_verif->execute([$tache_id, $id_session_user, $id_session_user, $id_session_user, $id_session_user]);
        if ($stmt_verif->fetch()) { $autorise = true; }
    }

    if ($autorise) {
        try {
            $lien->beginTransaction();

            // 1. 🎯 Mise à jour de la tâche avec persistance de la réassignation ($attribue_a)
            $sql_up = "UPDATE todo_taches SET texte = ?, statut = ?, date_echeance = ?, attribue_a = ? WHERE id = ?";
            $lien->prepare($sql_up)->execute([$texte_tache, $statut, $date_echeance, $attribue_a, $tache_id]);

            // 2. Nettoyage complet du pivot des catégories de cette tâche
            $sql_del_pivot = "DELETE FROM todo_tache_categories WHERE tache_id = ?";
            $lien->prepare($sql_del_pivot)->execute([$tache_id]);

            // 3. Réinsertion des multi-catégories
            if (!empty($categories_ids)) {
                $categories_ids = array_unique(array_map('intval', $categories_ids));
                
                $sql_ins_pivot = "INSERT INTO todo_tache_categories (tache_id, categorie_id) VALUES (?, ?)";
                $stmt_pivot = $lien->prepare($sql_ins_pivot);

                foreach ($categories_ids as $cat_id) {
                    if ($cat_id > 0) {
                        $stmt_pivot->execute([$tache_id, $cat_id]);
                    }
                }
            }

            $lien->commit();
            $_SESSION['succes_projet'] = "La tâche collaborative a été mise à jour et réassignée avec succès.";

        } catch (PDOException $e) {
            $lien->rollBack();
            $_SESSION['erreur_projet'] = "Erreur technique SQL lors de la modification : " . $e->getMessage();
        }
    } else {
        $_SESSION['erreur_projet'] = "Accès refusé : Vous ne faites pas partie de l'équipe autorisée à modifier cette tâche.";
    }
} else {
    $_SESSION['erreur_projet'] = "Le libellé de la tâche ne peut pas être vide.";
}

// Redirection MVC vers l'affichage du projet actif
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
