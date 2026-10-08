<?php
// services/modification_taches.php - Version Sécurisée Anti-Bug

$tache_id               = isset($_POST['tache_id']) ? (int)$_POST['tache_id'] : 0;
$projet_id              = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$texte_tache            = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$date_echeance          = (!empty($_POST['date_echeance'])) ? trim($_POST['date_echeance']) : null;
$statut                 = isset($_POST['statut']) ? (int)$_POST['statut'] : 0;

// 🎯 FUSION DE SÉCURITÉ : On intercepte les deux orthographes possibles (avec ou sans S)
$categories_ids = [];
if (isset($_POST['categories_ids']) && is_array($_POST['categories_ids'])) {
    $categories_ids = $_POST['categories_ids'];
} elseif (isset($_POST['categories_id']) && is_array($_POST['categories_id'])) {
    $categories_ids = $_POST['categories_id'];
}

$utilisateur_id = $_SESSION['user_id'] ?? 1;
$role           = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

if ($tache_id > 0 && !empty($texte_tache)) {
    
    if ($role === 'admin') {
        $stmt_verif = $lien->prepare("SELECT id FROM todo_taches WHERE id = ? LIMIT 1");
        $stmt_verif->execute([$tache_id]);
    } else {
        $stmt_verif = $lien->prepare("SELECT id FROM todo_taches WHERE id = ? AND utilisateur_id = ? LIMIT 1");
        $stmt_verif->execute([$tache_id, $utilisateur_id]);
    }

    if ($stmt_verif->fetch()) {
        try {
            $lien->beginTransaction();

            // 1. Mise à jour de la tâche
            $sql_up = "UPDATE todo_taches SET texte = ?, statut = ?, date_echeance = ? WHERE id = ?";
            $lien->prepare($sql_up)->execute([$texte_tache, $statut, $date_echeance, $tache_id]);

            // 2. Nettoyage complet
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
            $_SESSION['succes_projet'] = "Tâche mise à jour avec succès !";

        } catch (PDOException $e) {
            $lien->rollBack();
            $_SESSION['erreur_projet'] = "Erreur technique lors de la modification : " . $e->getMessage();
        }
    } else {
        $_SESSION['erreur_projet'] = "Accès refusé : Action non autorisée.";
    }
} else {
    $_SESSION['erreur_projet'] = "Le libellé de la tâche ne peut pas être vide.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
