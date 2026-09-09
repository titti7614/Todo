<?php
// services/supprimer_projet.php

// NETTOYAGE ABSOLU : On ne charge aucun fichier ici !
// Comme ce script est appelé PAR l'index.php, les variables $lien et $projet_id existent déjà.

if (isset($_POST['confirmer_suppression']) && $projet_id > 0) {
    // 1. Suppression manuelle en cascade ordonnée
    mysqli_query($lien, "DELETE FROM todo_list WHERE projet_id = $projet_id");
    mysqli_query($lien, "DELETE FROM todo_phases WHERE projet_id = $projet_id");
    mysqli_query($lien, "DELETE FROM todo_projets WHERE id = $projet_id");
    
    // 2. Redirection propre : l'entonnoir revient à zéro
    header("Location: index.php?action=liste");
    exit();
}

// Sécurité : si on arrive ici sans POST, on retourne simplement à la liste
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
