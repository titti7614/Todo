<?php
// services/ajouter_phases.php
// Script logique pure - Aucun code HTML

$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$nom_phase = isset($_POST['nom_phase']) ? trim($_POST['nom_phase']) : '';
$couleur_phase = isset($_POST['couleur_phase']) ? trim($_POST['couleur_phase']) : '#e67e22';

if ($projet_id > 0 && !empty($nom_phase)) {
    $phase_sec = mysqli_real_escape_string($lien, $nom_phase);
    $couleur_sec = mysqli_real_escape_string($lien, $couleur_phase);
    
    // 🎯 REQUÊTE CORRIGÉE : Utilisation du nom de champ réel 'projet_id'
    $sql = "INSERT INTO todo_phases (projet_id, nom, couleur) VALUES ($projet_id, '$phase_sec', '$couleur_sec')";
    
    try {
        if (mysqli_query($lien, $sql)) {
            $_SESSION['succes_projet'] = "La phase '$nom_phase' a été créée avec succès !";
        } else {
            $_SESSION['erreur_projet'] = "Erreur MySQL : " . mysqli_error($lien);
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $_SESSION['succes_projet'] = "La phase '$nom_phase' est déjà configurée pour ce projet.";
        } else {
            $_SESSION['erreur_projet'] = "Erreur technique SQL (Code " . $e->getCode() . ") : " . $e->getMessage();
        }
    }
} else {
    $_SESSION['erreur_projet'] = "Données incomplètes : le nom de la phase est obligatoire.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
