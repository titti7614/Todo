<?php
// services/ajouter_projets.php - Version V3 PDO (To-Do)

// NETTOYAGE OK : Aucune inclusion sauvage de base de données ici !
// Comme ce script est inclus par l'index.php, il possède déjà accès à la variable $lien et à la session.

if (isset($_POST['ajouter_projet']) && !empty(trim($_POST['nom_projet']))) {
    $nom_projet = trim($_POST['nom_projet']);
    
    // SÉCURITÉ MULTI-USER : On récupère l'ID de l'utilisateur connecté
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    // 1. On vérifie si ce projet existe déjà UNIQUEMENT POUR CET UTILISATEUR
    $query_check = "SELECT id FROM todo_projets WHERE nom_projet = ? AND utilisateur_id = ? LIMIT 1";
    $stmt_check = $lien->prepare($query_check);
    $stmt_check->execute([$nom_projet, $utilisateur_id]);
    $row = $stmt_check->fetch();
    
    if ($row) {
        // 🎯 CAS DU DOUBLON DÉTECTÉ : On extrait les informations
        $id_existant = $row['id'];
        $nom_encode = urlencode($nom_projet);
        
        // On redirige vers l'action spéciale de choix du routeur en passant les données dans l'URL
        header("Location: http://localhost:8000/index.php?action=choix_doublon&dup_id=" . $id_existant . "&dup_nom=" . $nom_encode);
        exit();
    } else {
        // Le nom est libre pour cet utilisateur, création classique avec injection de l'utilisateur_id
        $query_insert = "INSERT INTO todo_projets (nom_projet, utilisateur_id) VALUES (?, ?)";
        $stmt_insert = $lien->prepare($query_insert);
        
        if ($stmt_insert->execute([$nom_projet, $utilisateur_id])) {
            // Récupération du dernier ID inséré en PDO
            $nouveau_id = $lien->lastInsertId();
            
            // Redirection standard sur le nouveau projet créé
            header("Location: http://localhost:8000/index.php?projet_id=" . $nouveau_id . "&action=liste");
            exit();
        }
    }
}

// Sécurité par défaut si le script est accédé anormalement
header("Location: http://localhost:8000/index.php?action=liste");
exit();
