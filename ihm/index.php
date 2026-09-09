<?php
// ihm/index.php
session_start(); // 🎯 AJOUT ESSENTIEL : Active la mémoire flash pour les alertes


// 1. Activation de l'affichage des erreurs pour le débuggage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Chargement de la configuration et des services (Utilisation de require pour le routeur)
require __DIR__ . '/../conf/connexion_connect.php';
require __DIR__ . '/../services/projet_services.php';

if (file_exists(__DIR__ . '/../services/projet_tache.php')) {
    require __DIR__ . '/../services/projet_tache.php';
}

// 3. INITIALISATION DE LA CONNEXION À LA BASE
$lien = getTodoDatabaseConnection();

// 4. Initialisation des variables de routage et d'entonnoir (Regarde en GET puis en POST)
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : (isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0);
$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'liste'); 
$nom_fichier_actuel = basename($_SERVER['PHP_SELF']);

// 5. --- INTERCEPTION DU TRAITEMENT DES FORMULAIRES (POST) ---
// Ces scripts s'exécutent en arrière-plan avant tout affichage HTML

// Action : Ajouter un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_projet') {
    require_once __DIR__ . '/../services/ajouter_projet.php';
    exit();
}

// Action : Modifier un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modification_projet') {
    require_once __DIR__ . '/../services/modifier_projet.php';
    exit();
}

// Action : Supprimer un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suppression_projet') {
    require_once __DIR__ . '/../services/supprimer_projet.php';
    exit();
}

// Action : Ajouter une phase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_phase') {
    require_once __DIR__ . '/../services/ajouter_phase.php';
    exit();
}

// Action : Ajouter une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_tache') {
    require_once __DIR__ . '/../services/ajouter_tache.php';
    exit();
}

// 🎯 CORRECTIF ARCHITECTURE : Le routeur extrait les messages de la session
$message_erreur_projet = null;
if (isset($_SESSION['erreur_projet'])) {
    $message_erreur_projet = $_SESSION['erreur_projet'];
    unset($_SESSION['erreur_projet']); // Nettoyage immédiat côté logique
}

$message_succes_projet = null;
if (isset($_SESSION['succes_projet'])) {
    $message_succes_projet = $_SESSION['succes_projet'];
    unset($_SESSION['succes_projet']);
}
// 🔵 NOUVEAU : On extrait le message d'information pour le User C

// 🎯 CORRECTIF : Le routeur extrait les données du doublon directement depuis l'URL
$doublon_id = isset($_GET['dup_id']) ? (int)$_GET['dup_id'] : null;
$doublon_nom = isset($_GET['dup_nom']) ? trim($_GET['dup_nom']) : "";

// 6. --- PRÉPARATION DES DONNÉES DISPONIBLES POUR L'IHM (SERVICES) ---
// Appel aux fonctions pures de tes services pour alimenter l'interface

$liste_tous_projets = getTousProjets($lien);
$list_phases = $projet_id ? getPhasesParProjet($lien, $projet_id) : [];

// Recherche des entités spécifiques si demandées par l'URL (GET)
$tache_a_modifier = isset($_GET['id_tache']) ? getTachePourModification($lien, (int)$_GET['id_tache']) : null;

// Architecture Propre : C'est le service qui cherche le projet à supprimer ou modifier pour l'IHM
$projet_a_supprimer = ($action === 'suppression_projet' && $projet_id > 0) ? getProjetParId($lien, $projet_id) : null;
$projet_a_modifier = ($action === 'modification_projet' && $projet_id > 0) ? getProjetParId($lien, $projet_id) : null;



?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Central To-Do Multi-Projets</title>
    <!-- Liaison vers le dossier lib/ pour les styles -->
    <link rel="stylesheet" type="text/css" href="../lib/style.css">
</head>
<body>
<div class="container">
    <h1>📋 Central To-Do Multi-Projets</h1>

    <!-- ÉTAPE ÉNTONNOIR 1 : Le sélecteur global de projet toujours visible -->
    <?php include __DIR__ . '/../ihm/projet_liste.php'; ?>

    <!-- LE BANDEAU DE GESTION DU PROJET ACTIF (Apparaît si un projet est filtré) -->
    <?php if ($projet_id > 0): ?>
        <?php
        $nom_projet_courant = "Projet en cours";
        foreach ($liste_tous_projets as $p) {
            if ($p['id'] == $projet_id) {
                $nom_projet_courant = $p['nom_projet'];
                break;
            }
        }
        ?>
        <div class="project-badge-top" style="display: flex; justify-content: space-between; align-items: center; background: #e67e22; padding: 10px 15px; border-radius: 4px; margin-top: 20px; margin-bottom: 20px; color: white;">
            <span style="font-weight: bold;">📁 Projet actif : <?php echo htmlspecialchars($nom_projet_courant, ENT_QUOTES, 'UTF-8'); ?></span>
            <div>
                <!-- Liens hypertextes pour changer l'action du Routeur -->
                <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_projet" style="color: white; text-decoration: none; background: #3498db; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; margin-right: 5px; font-weight: bold;">✏️ Renommer</a>
                <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_projet" style="color: white; text-decoration: none; background: #c0392b; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;">🗑️ Supprimer le projet</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ZONE DE RENDU DYNAMIQUE DE L'IHM (Le Switch) -->
    <div class="zone-actions-ihm" style="margin-top: 20px;">
        

        <!-- L'IHM de l'index se contente d'afficher la variable sans traitement PHP -->
<?php if (!empty($message_info_projet)): ?>
    <div class="alert-info" style="background-color: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 4px; border: 1px solid #bee5eb; margin-bottom: 15px; font-weight: bold;">
        ℹ️ <?php echo htmlspecialchars($message_info_projet, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>
<?php
        switch ($action) {
            // --- COMPOSANTS IHM PROJETS ---
            case 'ajout_projet':
                include __DIR__ . '/ajout_projet.php';
                break;
                
            case 'modification_projet':
                include __DIR__ . '/modification_projet.php';
                break;
                
            case 'suppression_projet':
                include __DIR__ . '/suppression_projet.php';
                break;

            // --- COMPOSANTS IHM PHASES ---
            case 'ajout_phase':
                include __DIR__ . '/ajout_phase.php';
                break;
                
            case 'modification_phase':
                include __DIR__ . '/modification_phase.php';
                break;

            case 'choix_doublon':
                include __DIR__ . '/choix_projet_doublon.php';
                break;
                
            case 'suppression_phase':
                include __DIR__ . '/suppression_phase.php';
                break;

            // --- COMPOSANTS IHM TÂCHES ---
            case 'ajout_tache':
                include __DIR__ . '/ajout_tache.php';
                break;
                
            case 'modification_tache':
                include __DIR__ . '/modification_tache.php';
                break;
                
            case 'suppression_tache':
                include __DIR__ . '/suppression_tache.php';
                break;

            // --- VUE PAR DÉFAUT (L'entonnoir filtre ici) ---
            case 'liste':
            default:
                if ($projet_id > 0) {
                    // Si un projet est choisi, on appelle la vue d'affichage des tâches
                    include __DIR__ . '/projet_tache.php';
                } else {
                    // Message d'attente neutre
                    echo "<p style='text-align: center; color: #7f8c8d; font-style: italic; padding: 20px;'>
                            Veuillez sélectionner un projet dans le menu ci-dessus ou en créer un nouveau pour commencer.
                          </p>";
                }
                break;
        }
        ?>
    </div>
</div>
</body>
</html>
