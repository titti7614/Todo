<?php
// services/lister_taches.php
// Fonctions de lecture pures - Aucun HTML ici

/**
 * 1. Récupère la ressource brute mysqli_result des tâches d'un projet pour la liste d'accueil
 */
function getTachesParProjet($lien, $projet_id) {
    $projet_id = (int)$projet_id;
    
    // 🎯 CORRECTIF : Ajout de p.couleur AS couleur_phase dans le SELECT
    $sql = "SELECT t.id, t.texte, t.statut, p.nom AS nom_phase, p.couleur AS couleur_phase 
            FROM todo_taches t
            LEFT JOIN todo_phases p ON t.phase = p.id
            WHERE t.projet_id = $projet_id 
            ORDER BY t.id ASC";
            
    return mysqli_query($lien, $sql);
}


/**
 * 2. Récupère les données d'une seule tâche alignées sur vos vraies colonnes (id, projet_id, phase, texte, statut)
 */
function getTachePourModification($lien, $id_tache) {
    $id_tache = (int)$id_tache;
    
    // 🎯 ALIGNEMENT BDD : On extrait la colonne 'phase' réelle de votre table
    $sql = "SELECT id, projet_id, phase, texte, statut FROM todo_taches WHERE id = $id_tache LIMIT 1";
    $resultat = mysqli_query($lien, $sql);
    
    if ($resultat && mysqli_num_rows($resultat) > 0) {
        $donnees = mysqli_fetch_assoc($resultat);
        mysqli_free_result($resultat);
        return $donnees; // Renvoie le tableau contenant les vraies clés lues par l'IHM
    }
    
    return null;
}
?>
