<?php
// services/modifier_phases.php
// Script logique pure - Aucun code HTML

$projet_id     = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$phase_id      = isset($_POST['phase_id']) ? (int)$_POST['phase_id'] : 0;
$nom_phase     = isset($_POST['nom_phase']) ? trim($_POST['nom_phase']) : '';
$couleur_phase = isset($_POST['couleur_phase']) ? trim($_POST['couleur_phase']) : '#e67e22';

if ($phase_id > 0 && $projet_id > 0 && !empty($nom_phase)) {
    $nom_sec = mysqli_real_escape_string($lien, $nom_phase);
    $couleur_sec = mysqli_real_escape_string($lien, $couleur_phase);
    
    // 🎯 REQUÊTE SÉCURISÉE ET HARMONISÉE : Cible projet_id dans todo_phases
    $sql_update = "UPDATE todo_phases 
                   SET nom = '$nom_sec', couleur = '$couleur_sec' 
                   WHERE id = $phase_id AND projet_id = $projet_id"; 
                   
    try {
        if (mysqli_query($lien, $sql_update)) {
            $_SESSION['succes_projet'] = "La phase a été modifiée avec succès !";
        } else {
            $_SESSION['erreur_projet'] = "Erreur lors de la modification de la phase.";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $_SESSION['erreur_projet'] = "Erreur : La phase '$nom_phase' existe déjà pour ce projet.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique de base de données.";
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données du formulaire invalides ou incomplètes.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
