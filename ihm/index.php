<?php
// ihm/index.php
// 1. DÉMARRAGE DE LA SESSION (Mémoire flash pour les alertes)
session_start(); 

// 2. ACTIVATION DE L'AFFICHAGE DES ERREURS (Débuggage local)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. CHARGEMENT DE LA CONFIGURATION ET DES FONCTIONS SERVICES
require __DIR__ . '/../conf/connexion_connect.php';
require __DIR__ . '/../services/projet_services.php';

if (file_exists(__DIR__ . '/../services/lister_phases.php')) {
    require __DIR__ . '/../services/lister_phases.php';
}

if (file_exists(__DIR__ . '/../services/lister_taches.php')) {
    require __DIR__ . '/../services/lister_taches.php';
}

// 4. INITIALISATION DE LA CONNEXION À LA BASE
$lien = getTodoDatabaseConnection();

// 5. INITIALISATION DES VARIABLES DE ROUTAGE ET D'ENTONNOIR
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : (isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0);
$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'liste'); 
$nom_fichier_actuel = basename($_SERVER['PHP_SELF']);

// 6. --- INTERCEPTION DU TRAITEMENT DES FORMULAIRES (POST / ACTIONS LOGIQUES) ---
// Action : Ajouter un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_projets') {
    require_once __DIR__ . '/../services/ajouter_projets.php';
    exit();
}

// Action : Modifier un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modification_projets') {
    require_once __DIR__ . '/../services/modifier_projets.php';
    exit();
}

// Action : Supprimer un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suppression_projets') {
    require_once __DIR__ . '/../services/supprimer_projets.php';
    exit();
}

// Action : Ajouter une phase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_phases') {
    require_once __DIR__ . '/../services/ajouter_phases.php';
    exit();
}

// Action : Ajouter une tâche (Harmonisé au pluriel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_taches') {
    require_once __DIR__ . '/../services/ajouter_taches.php';
    exit();
}

// Action : Enregistrement de la modification d'une phase (POST uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modification_phases' && isset($_POST['nom_phase'])) {
    require_once __DIR__ . '/../services/modifier_phases.php';
    exit();
}


// Action : Enregistrement de la modification d'une tâche (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modification_taches') {
    require_once __DIR__ . '/../services/modifier_taches.php';
    exit();
}

// Action : Cocher / Décocher une tâche (Bascule dynamique du statut 0 <-> 1)
if (($action === 'cocher_tache' || $action === 'cocher') && isset($_GET['id_tache'])) {
    $id_tache_a_cocher = (int)$_GET['id_tache'];
    $sql_cocher = "UPDATE todo_taches SET statut = IF(statut = 1, 0, 1) WHERE id = $id_tache_a_cocher";
    mysqli_query($lien, $sql_cocher);
    $_SESSION['succes_projet'] = "Le statut de la tâche a été mis à jour.";
    header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
    exit();
}

// Action : Supprimer une tâche
if ($action === 'suppression_tache') {
    require_once __DIR__ . '/../services/supprimer_taches.php';
    exit();
}

// 7. --- EXTRACTEURS DES MESSAGES DE SESSIONS ET PARAMÈTRES GLOBAUX ---
$message_erreur_projet = null;
if (isset($_SESSION['erreur_projet'])) {
    $message_erreur_projet = $_SESSION['erreur_projet'];
    unset($_SESSION['erreur_projet']);
}

$message_succes_projet = null;
if (isset($_SESSION['succes_projet'])) {
    $message_succes_projet = $_SESSION['succes_projet'];
    unset($_SESSION['succes_projet']);
}

$doublon_id = isset($_GET['dup_id']) ? (int)$_GET['dup_id'] : null;
$doublon_nom = isset($_GET['dup_nom']) ? trim($_GET['dup_nom']) : "";

// 8. --- PRÉPARATION DES DONNÉES DISPONIBLES POUR L'IHM (SERVICES DE LECTURE) ---
$liste_tous_projets = getTousProjets($lien);
$list_phases = $projet_id ? getPhasesParProjet($lien, $projet_id) : [];
$resultat = $projet_id ? getTachesParProjet($lien, $projet_id) : null;

// Sécurisation du pré-remplissage pour les formulaires de modification (GET ou POST)
$tache_a_modifier = (isset($_GET['id_tache']) && function_exists('getTachePourModification')) ? getTachePourModification($lien, (int)$_GET['id_tache']) : null;

// 🎯 CAPTURE SÉCURISÉE EN REQUEST : Attrape l'ID que l'IHM l'envoie en GET ou en POST
$phase_id_contexte = isset($_REQUEST['phase_id']) ? (int)$_REQUEST['phase_id'] : 0;
$phase_a_modifier = ($action === 'modification_phases' && $phase_id_contexte > 0 && function_exists('getPhasePourModification')) ? getPhasePourModification($lien, $phase_id_contexte) : null;
// 🎯 CORRECTIF : Remplacement de 'suppression_projets' par 'suppression_projet' (au singulier)
$projet_a_supprimer = ($action === 'suppression_projets' && $projet_id > 0) ? getProjetParId($lien, $projet_id) : null;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Central To-Do Multi-Projets</title>
    <link rel="stylesheet" type="text/css" href="../lib/style.css">
</head>
<body>

<div class="container" style="position: relative;"> <!-- 🎯 Ajout de position: relative pour caler le badge -->
    
    <!-- 🏷️ Badge de Version 1.0 -->
    <div style="position: absolute; top: 10px; right: 10px; background: #64748b; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; letter-spacing: 0.5px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
        v1.0
    </div>

    <h1>📋 Central To-Do Multi-Projets</h1>

    <!-- BARRE GLOBALE : Choix du projet actif et formulaire rapide -->
    <div class="selector-box" style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between;">
        <div class="selector-section">
            <label for="proj_select"><strong>Projet actif :</strong></label>
            <select id="proj_select" onchange="window.location.href='<?php echo $nom_fichier_actuel; ?>?projet_id='+this.value;" style="padding: 5px; min-width: 200px;">
                <option value="0">-- Choisir un projet --</option>
                <?php if (!empty($liste_tous_projets)): ?>
                    <?php foreach ($liste_tous_projets as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $projet_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nom_projet'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <form action="<?php echo $nom_fichier_actuel; ?>?action=ajout_projets" method="POST" class="creation-section-inline">
            <input type="text" name="nom_projet" placeholder="Nom du nouveau projet..." required style="padding: 5px; width: 200px;">
            <button type="submit" name="ajouter_projet" class="btn-orange" style="background: #e67e22; color: white; padding: 5px 10px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">+ Créer</button>
        </form>
    </div>

    <!-- LE BANDEAU DE GESTION DU PROJET ACTIF -->
    <?php if ($projet_id > 0): ?>
        <?php
        $nom_projet_courant = "Projet en cours";
        if (!empty($liste_tous_projets)) {
            foreach ($liste_tous_projets as $p) {
                if ($p['id'] == $projet_id) {
                    $nom_projet_courant = $p['nom_projet'];
                    break;
                }
            }
        }
        ?>
        <div class="project-badge-top" style="display: flex; justify-content: space-between; align-items: center; background: #e67e22; padding: 10px 15px; border-radius: 4px; margin-top: 20px; margin-bottom: 20px; color: white;">
            <span style="font-weight: bold;">📁 Projet actif : <?php echo htmlspecialchars($nom_projet_courant, ENT_QUOTES, 'UTF-8'); ?></span>
            <div>
                <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_projets" style="color: white; text-decoration: none; background: #3498db; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; margin-right: 5px; font-weight: bold;">✏️ Renommer</a>
                <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_projets" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');" style="color: white; text-decoration: none; background: #c0392b; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;">🗑️ Supprimer le projet</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- 🎯 9. BARRE D'ACTIONS TECHNIQUE DU PROJET (Garantit la visibilité permanente du bouton d'ajout au pluriel) -->
    <?php if ($projet_id > 0 && $action === 'liste'): ?>
        <div class="barre-outils-projet" style="margin-top: 15px; margin-bottom: 15px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px dashed #3498db;">
            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=ajout_taches" class="btn-blue" style="display: inline-block; background: #3498db; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">
                + Ajouter une tâche
            </a>
        </div>
    <?php endif; ?>

    <!-- ZONE DE RENDU DYNAMIQUE DE L'IHM (Le Switch de routage) -->
    <div class="zone-actions-ihm" style="margin-top: 20px;">
        
        <?php if (!empty($message_succes_projet)): ?>
            <div class="alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; border: 1px solid #c3e6cb; margin-bottom: 15px; font-weight: bold;">
                ✓ <?php echo htmlspecialchars($message_succes_projet, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php
        switch ($action) {
            // --- COMPOSANTS IHM PROJETS ---
            case 'ajout_projets':
                include __DIR__ . '/ajout_projets.php';
                break;
                
            case 'modification_projets':
                include __DIR__ . '/modification_projets.php';
                break;
                
            case 'suppression_projets':
                include __DIR__ . '/suppression_projets.php';
                break;

            case 'choix_doublon':
                include __DIR__ . '/choix_projets_doublon.php';
                break;

             // --- COMPOSANTS IHM PHASES ---
            case 'liste_phases':
                include __DIR__ . '/liste_phases.php';
                break;

            case 'ajout_phases':
                include __DIR__ . '/ajout_phases.php';
                break;
                
            case 'modification_phases':
                // 🎯 CORRECTIF : Assurez-vous que ce nom correspond EXACTEMENT au nom de votre fichier dans le dossier ihm/
                // Si votre fichier s'appelle modification_tache.php ou modification_phases.php, écrivez son nom exact ici :
                include __DIR__ . '/modification_phases.php'; 
                break;

            // --- COMPOSANTS IHM TÂCHES (Synchronisé au pluriel) ---
            case 'ajout_taches':
                include __DIR__ . '/ajout_taches.php';
                break;
                
            case 'modification_taches':
                include __DIR__ . '/modification_taches.php';
                break;

            // --- VUE PAR DÉFAUT ---
            case 'liste':
            default:
                if ($projet_id > 0) {
                    include __DIR__ . '/liste_phases.php';
                    include __DIR__ . '/liste_taches.php';
                } else {
                    ?>
                    <p style="text-align: center; color: #7f8c8d; font-style: italic; padding: 20px;">
                        Aucun projet actif. Veuillez en choisir un.
                    </p>
                    <?php
                }
                break;
        }
        ?>
    </div>
</div>
</body>
</html>
