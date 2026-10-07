<?php
// index.php
// 1. DÉMARRAGE DE LA SESSION & VÉRIFICATION DU PORTAIL GLOBAL
session_start(); 

// MOCK DE SESSION POUR LE DÉVELOPPEMENT LOCAL
// On simule une connexion automatique pour éviter les boucles infinies de redirection
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'will';
}

$id_utilisateur_connecte = $_SESSION['user_id'];


// 2. ACTIVATION DE L'AFFICHAGE DES ERREURS (Débuggage local)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. CHARGEMENT DE LA CONFIGURATION ET DES FONCTIONS SERVICES (Chemins corrigés pour la racine)
require __DIR__ . '/conf/connexion_connect.php';
require __DIR__ . '/services/projet_services.php';

if (file_exists(__DIR__ . '/services/lister_categories.php')) {
    require __DIR__ . '/services/lister_categories.php';
}

if (file_exists(__DIR__ . '/services/lister_taches.php')) {
    require __DIR__ . '/services/lister_taches.php';
}

// 4. INITIALISATION DE LA CONNEXION À LA BASE (Désormais un objet PDO)
$lien = getTodoDatabaseConnection();

// 5. INITIALISATION DES VARIABLES DE ROUTAGE ET D'ENTONNOIR
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : (isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0);
$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'liste'); 
$nom_fichier_actuel = basename($_SERVER['PHP_SELF']);

// 6. --- INTERCEPTION DU TRAITEMENT DES FORMULAIRES (POST / ACTIONS LOGIQUES) ---
// Action : Ajouter un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_projets') {
    require_once __DIR__ . '/services/ajouter_projets.php';
    exit();
}

// Action : Modifier un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modification_projets') {
    require_once __DIR__ . '/services/modifier_projets.php';
    exit();
}

// Action : Supprimer un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suppression_projets') {
    require_once __DIR__ . '/services/supprimer_projets.php';
    exit();
}

// Action : Ajouter une categories
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_categories') {
    require_once __DIR__ . '/services/ajouter_categories.php';
    exit();
}

// Action : Ajouter une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_taches') {
    require_once __DIR__ . '/services/ajouter_taches.php';
    exit();
}

// Action : Enregistrement de la modification d'une catégorie (POST uniquement)
// 🎯 HARMONISATION NOMENCALTURE : On vérifie isset($_POST['nom_categories']) au lieu de nom_phase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modification_categories' && isset($_POST['nom_categories'])) {
    require_once __DIR__ . '/services/modifier_categories.php';
    exit();
}

// Action : Validation finale de suppression d'une categories (POST uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suppression_categories_confirmee') {
    require_once __DIR__ . '/services/supprimer_categories.php';
    exit();
}

// Action : Gère la modification tâche (POST uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'modification_taches' || $action === 'modification_tache')) {
    require_once __DIR__ . '/services/modifier_taches.php';
    exit();
}

// 🎯 v3.0 UPGRADE PDO : Cycle de statut à 3 états géré proprement en requêtes préparées PDO
if (($action === 'cocher_tache' || $action === 'cocher') && isset($_GET['id_tache'])) {
    $id_tache_a_cocher = (int)$_GET['id_tache'];
    
    // 1. Extraction du statut actuel en requêtes préparées PDO
    $stmt_statut = $lien->prepare("SELECT statut FROM todo_taches WHERE id = ? LIMIT 1");
    $stmt_statut->execute([$id_tache_a_cocher]);
    $tache_actuelle = $stmt_statut->fetch();
    
    $statut_actuel = isset($tache_actuelle['statut']) ? (int)$tache_actuelle['statut'] : 0;
    
    // 2. Calcul cyclique
    $nouveau_statut = ($statut_actuel + 1) % 3;
    
    // 3. Persistance de la valeur brute via UPDATE préparé PDO
    $stmt_update = $lien->prepare("UPDATE todo_taches SET statut = ? WHERE id = ?");
    $stmt_update->execute([$nouveau_statut, $id_tache_a_cocher]);
    
    $_SESSION['succes_projet'] = "Le niveau de maîtrise de la tâche a été mis à jour.";
    header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
    exit();
}

// Action : Validation finale de suppression d'une tâche (POST uniquement depuis l'écran rouge)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suppression_tache_confirmee') {
    require_once __DIR__ . '/services/supprimer_taches.php';
    exit();
}

// 🎯 v3.0 NOUVEAU : Action de sauvegarde rapide de la note textuelle d'une tâche (CORRIGÉ !)
if ($action === 'sauvegarder_note' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/services/sauvegarder_note.php';
    exit();
}

// 7. --- EXTRACTEURS DES MESSAGES DE SESSIONS AND PARAMÈTRES GLOBAUX ---
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
$list_categories = $projet_id ? getcategoriesParProjet($lien, $projet_id) : [];

// Interception des filtres multiples
$categories_selectionnees = (isset($_GET['categories_filtre']) && is_array($_GET['categories_filtre'])) ? $_GET['categories_filtre'] : [];
$recherche_mot_cle = isset($_GET['recherche_texte']) ? trim($_GET['recherche_texte']) : '';

// Chargement des tâches adaptées aux filtres croisés
$resultat = $projet_id ? getTachesParProjet($lien, $projet_id, $categories_selectionnees, $recherche_mot_cle) : null;

// Sécurisation du pré-remplissage pour les formulaires de modification
$tache_a_modifier = ((isset($_GET['id_tache']) || isset($_POST['id_tache'])) && ($action === 'modification_taches' || $action === 'modification_tache' || $action === 'suppression_tache')) ? getTachePourModification($lien, isset($_GET['id_tache']) ? (int)$_GET['id_tache'] : (int)$_POST['id_tache']) : null;

// Capture de l'ID de categories pour l'administration des categories
$categories_id_contexte = isset($_REQUEST['categories_id']) ? (int)$_REQUEST['categories_id'] : 0;
$categories_a_modifier = (($action === 'modification_categories' || $action === 'suppression_categories') && $categories_id_contexte > 0 && function_exists('getcategoriesPourModification')) ? getcategoriesPourModification($lien, $categories_id_contexte) : null;

$projet_a_supprimer = ($action === 'suppression_projets' && $projet_id > 0) ? getProjetParId($lien, $projet_id) : null;
$projet_a_modifier = ($action === 'modification_projets' && $projet_id > 0) ? getProjetParId($lien, $projet_id) : null;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Central To-Do Multi-Projets</title>
    <link rel="stylesheet" type="text/css" href="/lib/style.css">
</head>
<body>

<div class="container" style="position: relative;">
    
    <div style="position: absolute; top: 10px; right: 10px; background: #2ecc71; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; letter-spacing: 0.5px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
        v3.0
    </div>

    <h1>📋 Central To-Do Multi-Projets</h1>

    <div class="selector-box" style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
        <div class="selector-section" style="display: flex; align-items: center; gap: 10px;">
            
            <a href="http://localhost:8000/index.php?projet_id=<?php echo $projet_id; ?>&action=liste" 
               title="Réinitialiser les filtres et revenir à la liste complète" 
               style="display: inline-flex; align-items: center; justify-content: center; background: #34495e; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; height: 32px; box-sizing: border-box; transition: background 0.2s;"
               onmouseover="this.style.background='#2c3e50'" onmouseout="this.style.background='#34495e'">
               🏠 Accueil
            </a>

            <label for="proj_select"><strong>Projet actif :</strong></label>
            <select id="proj_select" onchange="window.location.href='http://localhost:8000/index.php?projet_id='+this.value;" style="padding: 5px; min-width: 200px; height: 32px;">
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

        <form action="http://localhost:8000/index.php?action=ajout_projets" method="POST" class="creation-section-inline" style="margin: 0; display: flex; gap: 5px;">
            <input type="text" name="nom_projet" placeholder="Nom du nouveau projet..." required style="padding: 5px; width: 200px; height: 32px; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 4px;">
            <button type="submit" name="ajouter_projet" class="btn-orange" style="background: #e67e22; color: white; padding: 0 10px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; height: 32px;">+ Créer</button>
        </form>
    </div>

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
            <!-- Ici la variable a été corrigée en $nom_projet_courant -->
            <span style="font-weight: bold;">📁 Projet actif : <?php echo htmlspecialchars($nom_projet_courant, ENT_QUOTES, 'UTF-8'); ?></span>
            <div>
                <a href="http://localhost:8000/index.php?projet_id=<?php echo $projet_id; ?>&action=modification_projets" style="color: white; text-decoration: none; background: #3498db; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; margin-right: 5px; font-weight: bold;">✏️ Renommer</a>
                <a href="http://localhost:8000/index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_projets" style="color: white; text-decoration: none; background: #c0392b; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;">🗑️ Supprimer le projet</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($projet_id > 0 && $action === 'liste'): ?>
        <div class="barre-outils-projet" style="margin-top: 15px; margin-bottom: 15px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px dashed #3498db;">
            <a href="http://localhost:8000/index.php?projet_id=<?php echo $projet_id; ?>&action=ajout_taches" class="btn-blue" style="display: inline-block; background: #3498db; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">
                + Ajouter une tâche
            </a>
        </div>
    <?php endif; ?>

    <div class="zone-actions-ihm" style="margin-top: 20px;">
        <?php if (!empty($message_succes_projet)): ?>
            <div class="alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; border: 1px solid #c3e6cb; margin-bottom: 15px; font-weight: bold;">
                ✓ <?php echo htmlspecialchars($message_succes_projet, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php
        switch ($action) {
            case 'ajout_projets':
                include __DIR__ . '/ihm/ajout_projets.php';
                break;
            case 'modification_projets':
                include __DIR__ . '/ihm/modification_projets.php';
                break;
            case 'suppression_projets':
                include __DIR__ . '/ihm/suppression_projets.php';
                break;
            case 'choix_doublon':
                include __DIR__ . '/ihm/choix_projets_doublon.php';
                break;
            case 'liste_categories':
                include __DIR__ . '/ihm/liste_categories.php';
                break;
            case 'ajout_categories':
                include __DIR__ . '/ihm/ajout_categories.php';
                break;
            case 'modification_categories':
                include __DIR__ . '/ihm/modification_categories.php'; 
                break;
            case 'suppression_categories':
                include __DIR__ . '/ihm/suppression_categories.php';
                break;
            case 'ajout_taches':
                include __DIR__ . '/ihm/ajout_taches.php';
                break;
            case 'modification_taches':
            case 'modification_tache': 
                include __DIR__ . '/ihm/modification_taches.php';
                break;
            case 'suppression_tache':
                include __DIR__ . '/ihm/suppression_taches.php';
                break;
            case 'liste':
            default:
                if ($projet_id > 0) {
                    include __DIR__ . '/ihm/liste_categories.php';
                    include __DIR__ . '/ihm/liste_taches.php';
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
