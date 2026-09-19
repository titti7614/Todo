<?php
// services/lister_taches.php
// Fonctions de lecture pures - Aucun HTML ici

/**
 * 1. Récupère les tâches d'un projet, avec un filtrage optionnel multi-phases
 * @param mysqli $lien Connexion à la BDD
 * @param int $projet_id ID du projet actif
 * @param array $phases_ids Tableau contenant les IDs des phases cochées
 */
function getTachesParProjet($lien, $projet_id, $phases_ids = []) {
    $projet_id = (int)$projet_id;
    
    // Base de la requête SQL avec jointure harmonisée
    $sql = "SELECT t.id, t.texte, t.statut, p.nom AS nom_phase, p.couleur AS couleur_phase 
            FROM todo_taches t
            LEFT JOIN todo_phases p ON t.phase_id = p.id
            WHERE t.projet_id = $projet_id";
            
    // 🎯 FILTRAGE DYNAMIQUE MULTI-PHASES
    // Si l'utilisateur a coché au moins une phase, on restreint les résultats
    if (!empty($phases_ids) && is_array($phases_ids)) {
        // Sécurisation de chaque ID pour éviter les injections SQL
        $phases_nettoyees = array_map('intval', $phases_ids);
        
        // Construction de la liste séparée par des virgules (ex: "3,7,12")
        $liste_in_sql = implode(',', $phases_nettoyees);
        
        // On ajoute la condition à la requête
        $sql .= " AND t.phase_id IN ($liste_in_sql)";
    }
    
    $sql .= " ORDER BY t.id ASC";
            
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
