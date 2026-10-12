<?php
// services/ajouter_taches.php — Version V4.0.5 Collaborative Blindée (Anti-Crash BDD)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛡️ Barrière de sécurité de session
if (!isset($_SESSION['user_id']) || !isset($lien)) {
    header("Location: index.php");
    exit();
}

$id_session_user = (int)$_SESSION['user_id'];
$role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

// Extraction et nettoyage des données POST
$projet_id       = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$date_echeance   = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
$attribue_a      = (!empty($_POST['attribue_a']) && (int)$_POST['attribue_a'] > 0) ? (int)$_POST['attribue_a'] : null;
$categories_id   = (isset($_POST['categories_ids']) && is_array($_POST['categories_ids'])) ? $_POST['categories_ids'] : [];

// Extraction stricte du nom 'texte_tache' issu de votre fichier HTML
$texte = '';
if (!empty($_POST['texte_tache'])) {
    $texte = trim($_POST['texte_tache']);
} elseif (!empty($_POST['texte'])) {
    $texte = trim($_POST['texte']);
}

if ($projet_id > 0 && $texte !== '') {
    
    // ======================================================================
    // 🧠 VÉRIFICATION DES DROITS D'ÉCRITURE COLLABORATIFS
    // ======================================================================
    $autorise = false;
    
    if ($role === 'admin') {
        $autorise = true;
    } else {
        // Validation que l'utilisateur appartient au groupe/cercle lié au projet
        $stmt_check = $lien->prepare("
            SELECT 1 FROM todo_projets pr
            LEFT JOIN todo_groupe_membres gm ON pr.groupe_id = gm.groupe_id
            WHERE pr.id = ? AND (pr.utilisateur_id = ? OR gm.utilisateur_id = ?)
            LIMIT 1
        ");
        $stmt_check->execute([$projet_id, $id_session_user, $id_session_user]);
        if ($stmt_check->fetch()) {
            $autorise = true;
        }
    }

    if ($autorise) {
        try {
            // 🎯 REQUÊTE SÉCURISÉE CONTRE LE MODE STRICT D'O2SWITCH
            // On sépare l'insertion selon la présence ou non des valeurs optionnelles pour ne pas faire crasher MySQL
            if ($date_echeance !== null && $attribue_a !== null) {
                $sql = "INSERT INTO todo_taches (projet_id, utilisateur_id, texte, statut, note, date_echeance, attribue_a) VALUES (?, ?, ?, 0, '', ?, ?)";
                $stmt = $lien->prepare($sql);
                $stmt->execute([$projet_id, $id_session_user, $texte, $date_echeance, $attribue_a]);
            } elseif ($date_echeance !== null) {
                $sql = "INSERT INTO todo_taches (projet_id, utilisateur_id, texte, statut, note, date_echeance, attribue_a) VALUES (?, ?, ?, 0, '', ?, NULL)";
                $stmt = $lien->prepare($sql);
                $stmt->execute([$projet_id, $id_session_user, $texte, $date_echeance]);
            } elseif ($attribue_a !== null) {
                $sql = "INSERT INTO todo_taches (projet_id, utilisateur_id, texte, statut, note, date_echeance, attribue_a) VALUES (?, ?, ?, 0, '', NULL, ?)";
                $stmt = $lien->prepare($sql);
                $stmt->execute([$projet_id, $id_session_user, $texte, $attribue_a]);
            } else {
                $sql = "INSERT INTO todo_taches (projet_id, utilisateur_id, texte, statut, note, date_echeance, attribue_a) VALUES (?, ?, ?, 0, '', NULL, NULL)";
                $stmt = $lien->prepare($sql);
                $stmt->execute([$projet_id, $id_session_user, $texte]);
            }
            
            $nouvelle_tache_id = $lien->lastInsertId();

            // Liaison des catégories associées
            if ($nouvelle_tache_id > 0 && !empty($categories_id)) {
                $sql_cat = "INSERT INTO todo_tache_categories (tache_id, categorie_id) VALUES (?, ?)";
                $stmt_cat = $lien->prepare($sql_cat);
                foreach ($categories_id as $cat_id) {
                    $stmt_cat->execute([$nouvelle_tache_id, (int)$cat_id]);
                }
            }

            $_SESSION['succes_projet'] = "La tâche a été ajoutée avec succès au projet collaboratif.";
            
        } catch (PDOException $e) {
            // Si la base de données renvoie une erreur malgré tout, on l'intercepte proprement pour éviter l'erreur 500
            $_SESSION['erreur_projet'] = "Erreur technique SQL lors de l'insertion : " . $e->getMessage();
        }
    } else {
        $_SESSION['erreur_projet'] = "Droits insuffisants : Vous ne faites pas partie du cercle de confiance de ce projet.";
    }
} else {
    $_SESSION['erreur_projet'] = "Veuillez renseigner le libellé de la tâche.";
}

// Redirection MVC standard vers la liste
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
