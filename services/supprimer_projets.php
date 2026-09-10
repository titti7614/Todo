<?php
// services/supprimer_projets.php

// Comme ce script est appelé PAR l'index.php, les variables $lien et $projet_id existent déjà.

if (isset($_POST['confirmer_suppression']) && $projet_id > 0) {
    // 🎯 CORRECTIF : Remplacement de todo_list par todo_taches (le nom réel de votre table)
    mysqli_query($lien, "DELETE FROM todo_taches WHERE projet_id = $projet_id");
    mysqli_query($lien, "DELETE FROM todo_phases WHERE projet_id = $projet_id");
    mysqli_query($lien, "DELETE FROM todo_projets WHERE id = $projet_id");
    
    // 2. Redirection propre : l'entonnoir revient à zéro
    $_SESSION['succes_projet'] = "Le projet ainsi que toutes ses phases et tâches ont été supprimés.";
    header("Location: index.php?action=liste");
    exit();
}

// Sécurité : si on arrive ici sans POST, on retourne simplement à la liste
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
