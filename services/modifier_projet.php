<?php
// services/ajouter_projet.php

if (isset($_POST['ajouter_projet']) && !empty(trim($_POST['nom_projet']))) {
    $nom_projet = mysqli_real_escape_string($lien, trim($_POST['nom_projet']));
    
    // 1. On vérifie si ce projet existe déjà
    $check = mysqli_query($lien, "SELECT id FROM todo_projets WHERE nom_projet = '$nom_projet'");
    
    if ($row = mysqli_fetch_assoc($check)) {
        // 🎯 CAS DU DOUBLON DETECTÉ : On mémorise les infos pour le choix de l'utilisateur
        $_SESSION['doublon_projet_id'] = $row['id'];
        $_SESSION['doublon_projet_nom'] = $nom_projet;
        
        // On redirige vers une action spéciale de choix sur le routeur
        header("Location: index.php?action=choix_doublon");
        exit();
    } else {
        // Le nom est libre, création classique
        $sql = "INSERT INTO todo_projets (nom_projet) VALUES ('$nom_projet')";
        if (mysqli_query($lien, $sql)) {
            $nouveau_id = mysqli_insert_id($lien);
            header("Location: index.php?projet_id=" . $nouveau_id . "&action=liste");
            exit();
        }
    }
}

header("Location: index.php?action=liste");
exit();
