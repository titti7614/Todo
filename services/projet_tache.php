<?php
// /Users/Mac/Documents/Dev/todo/services/projet_tache.php

function getTachesParProjet($lien, $projet_id) {
    $taches = [];
    $projet_id = (int)$projet_id;
    
    // CORRECTION : On sélectionne 'texte', 'phase' et 'statut' à la place de 'nom'
    $query = "SELECT id, texte, phase, statut FROM todo_list WHERE projet_id = $projet_id ORDER BY phase ASC, date_creation DESC";
    
    $result = mysqli_query($lien, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $taches[] = $row;
        }
    }
    return $taches;
}
