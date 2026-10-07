<?php
// services/ajouter_projets.php - Version V3 PDO (To-Do)

if (isset($_POST['ajouter_projet']) && !empty(trim($_POST['nom_projet']))) {
    $nom_projet = trim($_POST['nom_projet']);
    
    // SÉCURITÉ MULTI-USER : Utilisation stricte de l'ID authentifié par le portail
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    // 1. On vérifie si ce projet existe déjà UNIQUEMENT POUR CET UTILISATEUR
    $query_check = "SELECT id FROM todo_projets WHERE nom_projet = ? AND utilisateur_id = ? LIMIT 1";
    $stmt_check = $lien->prepare($query_check);
    $stmt_check->execute([$nom_projet, $utilisateur_id]);
    $row = $stmt_check->fetch();
    
    if ($row) {
        $id_existant = $row['id'];
        $nom_encode = urlencode($nom_projet);
        
        // Redirection relative corrigée
        header("Location: index.php?action=choix_doublon&dup_id=" . $id_existant . "&dup_nom=" . $nom_encode);
        exit();
    } else {
        // Le nom est libre pour cet utilisateur, création avec liaison de l'utilisateur_id
        $query_insert = "INSERT INTO todo_projets (nom_projet, utilisateur_id) VALUES (?, ?)";
        $stmt_insert = $lien->prepare($query_insert);
        
        if ($stmt_insert->execute([$nom_projet, $utilisateur_id])) {
            $nouveau_id = $lien->lastInsertId();
            
            header("Location: index.php?projet_id=" . $nouveau_id . "&action=liste");
            exit();
        }
    }
}

header("Location: index.php?action=liste");
exit();
