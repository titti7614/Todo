<?php
// services/lister_phases.php
// Fonctions de lecture pures pour les phases

/**
 * 1. Récupère toutes les phases uniques rattachées à un projet pour le menu déroulant
 */
function getPhasesParProjet($lien, $projet_id) {
    $projet_id = (int)$projet_id;
    
    // Requête pure sur votre table de phases dédiée
    $sql = "SELECT id, nom FROM todo_phases WHERE projet_id = $projet_id ORDER BY id ASC";
    $resultat = mysqli_query($lien, $sql);
    
    $tableau_phases = [];
    if ($resultat) {
        while ($ligne = mysqli_fetch_assoc($resultat)) {
            $tableau_phases[] = $ligne;
        }
        mysqli_free_result($resultat);
    }
    return $tableau_phases;
}

/**
 * 2. 🎯 CORRECTIF CHIRURGICAL : Récupère les données d'une seule phase pour alimenter l'IHM
 */
function getPhasePourModification($lien, $phase_id) {
    $phase_id = (int)$phase_id;
    
    // Aligné sur vos colonnes réelles : id, nom, projet_id de la table todo_phases
    $sql = "SELECT id, nom, projet_id FROM todo_phases WHERE id = $phase_id LIMIT 1";
    $resultat = mysqli_query($lien, $sql);
    
    if ($resultat && mysqli_num_rows($resultat) > 0) {
        $donnees = mysqli_fetch_assoc($resultat);
        mysqli_free_result($resultat);
        return $donnees; // Renvoie bien le tableau contenant les clés 'id' et 'nom'
    }
    return null;
}
?>
