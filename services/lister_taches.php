<?php
// services/lister_taches.php

/**
 * 1. Récupère les tâches d'un projet avec double filtrage et tri intelligent (en cours d'abord)
 */
function getTachesParProjet($lien, $projet_id, $phases_ids = [], $recherche_texte = '') {
    $projet_id = (int)$projet_id;
    
    $sql = "SELECT t.id, t.texte, t.statut, p.nom AS nom_phase, p.couleur AS couleur_phase 
            FROM todo_taches t
            LEFT JOIN todo_phases p ON t.phase_id = p.id
            WHERE t.projet_id = $projet_id";
            
    // FILTRE 1 : Multi-phases (Cases à cocher)
    if (!empty($phases_ids) && is_array($phases_ids)) {
        $phases_nettoyees = array_map('intval', $phases_ids);
        $liste_in_sql = implode(',', $phases_nettoyees);
        $sql .= " AND t.phase_id IN ($liste_in_sql)";
    }
    
    // FILTRE 2 : Barre de recherche textuelle
    if (!empty($recherche_texte)) {
        $recherche_sec = mysqli_real_escape_string($lien, $recherche_texte);
        $sql .= " AND t.texte LIKE '%$recherche_sec%'";
    }
    
    // 🎯 TRI INTELLIGENT : Statut 0 (en cours) d'abord, puis statut 1 (fait), et enfin par ID du plus récent au plus ancien
    $sql .= " ORDER BY t.statut ASC, t.id DESC";
            
    return mysqli_query($lien, $sql);
}

/**
 * 2. Récupère les données d'une seule tâche alignées sur la nouvelle colonne (phase_id)
 */
function getTachePourModification($lien, $id_tache) {
    $id_tache = (int)$id_tache;
    
    // 🎯 HARMONISATION : Remplacement de la colonne 'phase' obsolète par 'phase_id'
    $sql = "SELECT id, projet_id, phase_id, texte, statut FROM todo_taches WHERE id = $id_tache LIMIT 1";
    $resultat = mysqli_query($lien, $sql);
    
    if ($resultat && mysqli_num_rows($resultat) > 0) {
        $donnees = mysqli_fetch_assoc($resultat);
        mysqli_free_result($resultat);
        return $donnees; // Renvoie le tableau contenant les clés lues par l'IHM
    }
    
    return null;
}
?>
