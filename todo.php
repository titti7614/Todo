<?php
// Active l'affichage des erreurs pour traquer un éventuel problème de serveur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. On inclut le fichier de configuration propre au To-Do pour charger la fonction
require_once __DIR__ . '/conf/connexion_connect.php';

// 2. On appelle la fonction pour initialiser la connexion à la base centrale
$lien = getTodoDatabaseConnection();

// Déterminer le nom du fichier actuel pour la redirection dynamique
$nom_fichier_actuel = basename($_SERVER['PHP_SELF']);

// ** CORRECTION : Initialisation de la variable pour éviter l'erreur "Undefined variable" **
$tache_a_modifier = null;

// Traitement : Si l'utilisateur crée un NOUVEAU PROJET
if (isset($_POST['ajouter_projet']) && !empty(trim($_POST['nom_projet']))) {
    $nom_projet = mysqli_real_escape_string($lien, trim($_POST['nom_projet']));
    
    // Insertion du nouveau projet
    $sql_projet = "INSERT INTO todo_projets (nom_projet) VALUES ('$nom_projet')";
    if (mysqli_query($lien, $sql_projet)) {
        // Récupération de l'ID du projet tout juste créé
        $nouveau_projet_id = mysqli_insert_id($lien);
        // Redirection dynamique vers le nouveau projet
        header("Location: " . $nom_fichier_actuel . "?projet_id=" . $nouveau_projet_id);
        exit();
    }
}

// Récupération du projet via l'URL (GET) ou les formulaires (POST)
$projet_id = 0;
if (isset($_GET['projet_id'])) {
    $projet_id = (int)$_GET['projet_id'];
} elseif (isset($_POST['projet_id'])) {
    $projet_id = (int)$_POST['projet_id'];
}

// Sécurité : On récupère les informations du projet pour valider qu'il existe
$nom_projet_courant = "Projet inconnu";
if ($projet_id > 0) {
    $check_projet = mysqli_query($lien, "SELECT nom_projet FROM todo_projets WHERE id = $projet_id");
    if ($row = mysqli_fetch_assoc($check_projet)) {
        $nom_projet_courant = $row['nom_projet'];
    } else {
        $projet_id = 0;
    }
}

// Liste pour proposer un choix à l'utilisateur dans le menu déroulant
$liste_tous_projets = mysqli_query($lien, "SELECT * FROM todo_projets ORDER BY nom_projet ASC");

// 1. TRAITEMENT : Si l'utilisateur ajoute une NOUVELLE PHASE
if (isset($_POST['ajouter_phase']) && !empty(trim($_POST['nom_phase'])) && $projet_id > 0) {
    $nom_phase = mysqli_real_escape_string($lien, trim($_POST['nom_phase']));
    $sql_phase = "INSERT IGNORE INTO todo_phases (projet_id, nom) VALUES ($projet_id, '$nom_phase')";
    mysqli_query($lien, $sql_phase);
    header("Location: " . $nom_fichier_actuel . "?projet_id=" . $projet_id);
    exit();
}



// 2.5 TRAITEMENT : Si l'utilisateur enregistre la MODIFICATION d'une tâche
if (isset($_POST['modifier_tache']) && !empty(trim($_POST['texte_tache'])) && $projet_id > 0) {
    $id_tache = (int)$_POST['id_tache'];
    $texte = mysqli_real_escape_string($lien, trim($_POST['texte_tache']));
    $phase = mysqli_real_escape_string($lien, $_POST['phase_tache']);

    $sql_update = "UPDATE todo_list SET texte = '$texte', phase = '$phase' WHERE id = $id_tache AND projet_id = $projet_id";
    mysqli_query($lien, $sql_update);
    header("Location: " . $nom_fichier_actuel . "?projet_id=" . $projet_id);
    exit();
}

// 3. TRAITEMENT : Si l'utilisateur coche une tâche comme faite
if (isset($_GET['action']) && $_GET['action'] == 'cocher' && isset($_GET['id']) && $projet_id > 0) {
    $id_tache = (int)$_GET['id'];
    $sql_cocher = "UPDATE todo_list SET statut = 1 WHERE id = $id_tache AND projet_id = $projet_id";
    mysqli_query($lien, $sql_cocher);
    header("Location: " . $nom_fichier_actuel . "?projet_id=" . $projet_id);
    exit();
}

// 3.5 TRAITEMENT : Si l'utilisateur supprime un PROJET
if (isset($_GET['action']) && $_GET['action'] == 'supprimer_projet' && $projet_id > 0) {
    mysqli_query($lien, "DELETE FROM todo_list WHERE projet_id = $projet_id");
    mysqli_query($lien, "DELETE FROM todo_phases WHERE projet_id = $projet_id");
    mysqli_query($lien, "DELETE FROM todo_projets WHERE id = $projet_id");
    header("Location: " . $nom_fichier_actuel);
    exit();
}

// 4. RÉCUPÉRATION : On charge les données spécifiques au projet sélectionné
$list_phases = false;
$resultat = false;
$tab_phases = [];

if ($projet_id > 0) {
    $list_phases = mysqli_query($lien, "SELECT * FROM todo_phases WHERE projet_id = $projet_id ORDER BY nom ASC");
    
    // Remplissage du tableau associatif des phases pour la sécurité d'affichage
    while ($p = mysqli_fetch_assoc($list_phases)) {
        $tab_phases[$p['id']] = $p['nom'];
    }
    if (mysqli_num_rows($list_phases) > 0) {
        mysqli_data_seek($list_phases, 0);
    }

    // Récupération de la tâche à modifier si demandée
    if (isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id'])) {
        $id_tache_modif = (int)$_GET['id'];
        $res_modif = mysqli_query($lien, "SELECT * FROM todo_list WHERE id = $id_tache_modif AND projet_id = $projet_id");
        $tache_a_modifier = mysqli_fetch_assoc($res_modif);
    }

    $sql_liste = "SELECT * FROM todo_list WHERE projet_id = $projet_id ORDER BY phase ASC, date_creation DESC";
    $resultat = mysqli_query($lien, $sql_liste);
}
?>

