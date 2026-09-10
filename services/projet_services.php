<?php
// services/projet_services.php

function getTousProjets($lien) {
    $projets = [];
    $query = "SELECT id, nom_projet FROM todo_projets ORDER BY nom_projet ASC";
    $result = mysqli_query($lien, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $projets[] = $row;
        }
    }
    return $projets;
}

// function getPhasesParProjet($lien, $projet_id) {
//     $phases = [];
//     $projet_id = (int)$projet_id;
//     $query = "SELECT id, nom FROM todo_phases WHERE projet_id = $projet_id ORDER BY nom ASC";
//     $result = mysqli_query($lien, $query);
//     if ($result) {
//         while ($row = mysqli_fetch_assoc($result)) {
//             $phases[] = $row;
//         }
//     }
//     return $phases;
// }

function getProjetParId($lien, $projet_id) {
    $projet_id = (int)$projet_id;
    $query = "SELECT id, nom_projet FROM todo_projets WHERE id = $projet_id LIMIT 1";
    $result = mysqli_query($lien, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}
