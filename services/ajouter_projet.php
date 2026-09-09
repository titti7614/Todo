<?php
// services/ajouter_projet.php

// NETTOYAGE OK : Aucune inclusion sauvage de base de données ici !
// Comme ce script est inclus par l'index.php, il possède déjà accès à la variable $lien et $action.

if (isset($_POST['ajouter_projet']) && !empty(trim($_POST['nom_projet']))) {
    $nom_projet = mysqli_real_escape_string($lien, trim($_POST['nom_projet'])); //
    
    // 1. On vérifie si ce projet existe déjà en base de données
    $check = mysqli_query($lien, "SELECT id FROM todo_projets WHERE nom_projet = '$nom_projet'"); //
    
    if ($row = mysqli_fetch_assoc($check)) { //
        // 🎯 CAS DU DOUBLON DETECTÉ : On extrait les informations
        $id_existant = $row['id']; //
        $nom_encode = urlencode($nom_projet);
        
        // On redirige vers l'action spéciale de choix du routeur en passant les données dans l'URL
        header("Location: index.php?action=choix_doublon&dup_id=" . $id_existant . "&dup_nom=" . $nom_encode); //
        exit(); //
    } else {
        // Le nom est libre, création classique
        $sql = "INSERT INTO todo_projets (nom_projet) VALUES ('$nom_projet')"; //
        if (mysqli_query($lien, $sql)) { //
            $nouveau_id = mysqli_insert_id($lien); //
            
            // Redirection standard sur le nouveau projet créé
            header("Location: index.php?projet_id=" . $nouveau_id . "&action=liste"); //
            exit(); //
        }
    }
}

// Sécurité par défaut si le script est accédé anormalement
header("Location: index.php?action=liste"); //
exit(); //
