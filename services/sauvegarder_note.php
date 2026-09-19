<?php
// services/sauvegarder_note.php
// Script logique pure - Aucun code HTML ici

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Connexion absolue à la base de données
require_once dirname(__DIR__) . "/conf/connexion_connect.php";
$lien = getTodoDatabaseConnection();

// 2. Récupération sécurisée des données envoyées par le tiroir HTML
$projet_id  = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache   = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$note_texte = isset($_POST['note_texte']) ? trim($_POST['note_texte']) : '';

if ($id_tache > 0) {
    // Sécurisation de la chaîne pour éviter les injections et bugs d'apostrophes
    $note_sec = mysqli_real_escape_string($lien, $note_texte);
    
    // Requête d'enregistrement
    $sql = "UPDATE todo_taches SET note = '$note_sec' WHERE id = $id_tache";
    $exec = mysqli_query($lien, $sql);
    
    // Sécurité : Si la colonne n'avait pas été créée, on la crée à la volée
    if (!$exec && mysqli_errno($lien) === 1054) {
        mysqli_query($lien, "ALTER TABLE todo_taches ADD COLUMN note TEXT NULL");
        mysqli_query($lien, $sql); // On re-exécute la sauvegarde
    }
    
    $_SESSION['succes_projet'] = "Note enregistrée avec succès !";
} else {
    $_SESSION['erreur_projet'] = "Identifiant de tâche invalide.";
}

// 3. Redirection propre vers le projet en cours
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
