<?php
// services/projet_services.php - Version V3 PDO (To-Do)

/**
 * Récupère tous les projets de l'utilisateur connecté
 */
function getTousProjets($lien) {
    // SÉCURITÉ : On récupère l'ID de la personne connectée via la session du portail
    $utilisateur_id = $_SESSION['user_id'] ?? 0;
    
    // On filtre STRICTEMENT pour ne pas afficher les projets du voisin
    $query = "SELECT id, nom_projet FROM todo_projets WHERE utilisateur_id = ? ORDER BY nom_projet ASC";
    
    $stmt = $lien->prepare($query);
    $stmt->execute([$utilisateur_id]);
    
    return $stmt->fetchAll(); // Retourne directement le tableau associatif complet
}

/**
 * Récupère un projet spécifique par son ID (en vérifiant qu'il appartient bien à l'utilisateur)
 */
function getProjetParId($lien, $projet_id) {
    $utilisateur_id = $_SESSION['user_id'] ?? 0;
    
    // Double sécurité : l'ID du projet ET l'ID de l'utilisateur doivent correspondre
    $query = "SELECT id, nom_projet FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1";
    
    $stmt = $lien->prepare($query);
    $stmt->execute([$projet_id, $utilisateur_id]);
    $projet = $stmt->fetch();
    
    return $projet ? $projet : null;
}
