<?php
// services/ajouter_projets.php — Version V4.0.0 Collaborative Pure MVC (Livraison Officielle)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛡️ Barrière de sécurité de session globale
if (!isset($_SESSION['user_id']) || !isset($lien)) {
    header("Location: index.php");
    exit();
}

$id_session_user = (int)$_SESSION['user_id'];

// Extraction et nettoyage des données POST
$nom_projet = isset($_POST['nom_projet']) ? trim($_POST['nom_projet']) : '';
// 🎯 Nouveauté V4.0.0 : Récupération du cercle sélectionné (optionnel si projet solo)
$groupe_id  = (!empty($_POST['groupe_id_selectionne']) && (int)$_POST['groupe_id_selectionne'] > 0) ? (int)$_POST['groupe_id_selectionne'] : null;

if (!empty($nom_projet)) {
    try {
        // 1. Double détection anti-doublon sémantique sur le nom du projet
        $stmt_verif = $lien->prepare("SELECT id FROM todo_projets WHERE nom_projet = ? AND utilisateur_id = ? LIMIT 1");
        $stmt_verif->execute([$nom_projet, $id_session_user]);
        
        if ($stmt_verif->fetch()) {
            $_SESSION['erreur_projet'] = "Un projet portant exactement le même nom existe déjà dans votre espace.";
        } else {
            // 2. 🛡️ Insertion sécurisée avec injection du verrou de Groupe ID de la V4.0.0
            $sql = "INSERT INTO todo_projets (nom_projet, utilisateur_id, groupe_id) VALUES (?, ?, ?)";
            $stmt = $lien->prepare($sql);
            $stmt->execute([$nom_projet, $id_session_user, $groupe_id]);
            
            $nouveau_projet_id = $lien->lastInsertId();
            
            $_SESSION['succes_projet'] = "Le nouveau projet collaboratif a été créé avec succès.";
            
            // Redirection immédiate sur le projet fraîchement créé pour fluidifier l'IHM
            header("Location: index.php?projet_id=" . $nouveau_projet_id . "&action=liste");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique SQL lors de la création : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Veuillez renseigner un nom de projet valide.";
}

header("Location: index.php?projet_id=0&action=liste");
exit();
