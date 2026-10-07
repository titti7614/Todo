<?php
// services/projet_services.php - Version V3 PDO (To-Do)

/**
 * Récupère tous les projets de l'utilisateur connecté
 */
function getTousProjets($lien) {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    $role = $_SESSION['mes_apps_cache']['todo'] ?? 'user';
    
    // Si ADMIN : On charge TOUS les projets de la base
    if ($role === 'admin') {
        $query = "SELECT id, nom_projet FROM todo_projets ORDER BY nom_projet ASC";
        $stmt = $lien->prepare($query);
        $stmt->execute();
    } else {
        // Si USER : Verrou strict sur ses propres projets
        $query = "SELECT id, nom_projet FROM todo_projets WHERE utilisateur_id = ? ORDER BY nom_projet ASC";
        $stmt = $lien->prepare($query);
        $stmt->execute([$utilisateur_id]);
    }
    
    return $stmt->fetchAll();
}


/**
 * Récupère un projet spécifique par son ID (en vérifiant qu'il appartient bien à l'utilisateur)
 */
function getProjetParId($lien, $projet_id) {
    $utilisateur_id = $_SESSION['user_id'] ?? 1;
    
    $query = "SELECT id, nom_projet FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1";
    
    $stmt = $lien->prepare($query);
    $stmt->execute([$projet_id, $utilisateur_id]);
    $projet = $stmt->fetch();
    
    return $projet ? $projet : null;
}
?>
