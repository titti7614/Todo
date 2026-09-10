<?php
// services/modifier_projets.php
// Script logique pure - Aucun code HTML ici

// 1. Récupération des données postées par le formulaire de renommage
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
// Attention à l'index utilisé dans votre formulaire (ici accordé avec le correctif du champ texte)
$nouveau_nom = isset($_POST['nouveau_nom_projet']) ? trim($_POST['nouveau_nom_projet']) : '';

if ($projet_id > 0 && !empty($nouveau_nom)) {
    $nom_sec = mysqli_real_escape_string($lien, $nouveau_nom);
    
    // 2. Requête SQL de mise à jour sur votre table todo_projets
    $sql = "UPDATE todo_projets SET nom_projet = '$nom_sec' WHERE id = $projet_id";
    
    try {
        if (mysqli_query($lien, $sql)) {
            $_SESSION['succes_projet'] = "Le projet a été renommé en '$nouveau_nom' avec succès !";
        } else {
            $_SESSION['erreur_projet'] = "Erreur lors du renommage du projet.";
        }
    } catch (mysqli_sql_exception $e) {
        // Gestion des cas où le nouveau nom ferait doublon avec un autre projet existant
        if ($e->getCode() === 1062) {
            $_SESSION['erreur_projet'] = "Un projet porte déjà le nom '$nouveau_nom'.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique de base de données.";
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données incomplètes pour modifier le projet.";
}

// 3. Redirection propre vers la liste du projet modifié
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
