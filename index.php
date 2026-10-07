<?php
// index.php (Application To-Do V3 — SÉCURITÉ PRODUCTION) — PARTIE 1

// 1. INITIALISATION DE L'ÉCOUTEUR DE SESSION GLOBAL DE LA PLATEFORME
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// ======================================================================
// 🛠️ BLOC DE DÉVELOPPEMENT LOCAL (MAC / PC)
// Pour développer sur votre Mac : Supprimez les " // " des 3 lignes ci-dessous.
// Pour la production o2switch : Laissez les " // " pour bloquer l'accès.
// ======================================================================
// $_SESSION['user_id'] = 1; 
// $_SESSION['mes_apps_cache']['todo'] = 'admin'; // 'admin' ou 'user'
// ======================================================================

// 🛡️ BARRIÈRE DE SÉCURITÉ INTER-DOSSIERS STRICTE (Production will-apps.fr)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['mes_apps_cache']['todo'])) {
    // Expulsion immédiate et sécurisée vers la mire de connexion du portail
    header('Location: /portail/index.php', true, 302);
    exit();
}

// Hydratation de l'ID utilisateur connecté fourni par le portail pour le cloisonnement
$id_utilisateur_connecte = (int)$_SESSION['user_id'];

// 2. DÉSACTIVATION DES ERREURS VISIBLES POUR LA PRODUCTION (Sécurité o2switch)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// 3. CHARGEMENT DE LA CONFIGURATION ET DES SERVICES
require_once __DIR__ . '/conf/connexion_connect.php';
require_once __DIR__ . '/services/projet_services.php';

if (file_exists(__DIR__ . '/services/lister_categories.php')) {
    require_once __DIR__ . '/services/lister_categories.php';
}

if (file_exists(__DIR__ . '/services/lister_taches.php')) {
    require_once __DIR__ . '/services/lister_taches.php';
}

// 4. INITIALISATION DE LA CONNEXION À LA BASE DE DONNÉES (Objet PDO global)
$lien = getTodoDatabaseConnection();

// 5. INITIALISATION DES VARIABLES DE ROUTAGE ET D'ENTONNOIR
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : (isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0);
$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'liste'); 
$nom_fichier_actuel = basename($_SERVER['PHP_SELF']);

// 6. --- INTERCEPTION DU TRAITEMENT DES FORMULAIRES (POST / ACTIONS LOGIQUES MULTI-USER) ---

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

// Action : Ajouter une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_categories') {
    require_once __DIR__ . '/services/ajouter_categories.php';
    exit();
}

// Action : Ajouter une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'ajout_taches') {
    require_once __DIR__ . '/services/ajouter_taches.php';
    exit();
}

// Action : Enregistrement de la modification d'une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modification_categories' && isset($_POST['nom_categories'])) {
    require_once __DIR__ . '/services/modifier_categories.php';
    exit();
}

// Action : Validation de suppression d'une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suppression_categories_confirmee') {
    require_once __DIR__ . '/services/supprimer_categories.php';
    exit();
}

// Action : Modifier une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'modification_taches' || $action === 'modification_tache')) {
    require_once __DIR__ . '/services/modifier_taches.php';
    exit();
}

// 🎯 Cycle de statut à 3 états sécurisé : Cloisonnement strict via $id_utilisateur_connecte
if (($action === 'cocher_tache' || $action === 'cocher') && isset($_GET['id_tache'])) {
    $id_tache_a_cocher = (int)$_GET['id_tache'];
    
    // 1. Extraction du statut avec double verrou : ID tâche et ID utilisateur connecté
    $stmt_statut = $lien->prepare("SELECT statut FROM todo_taches WHERE id = ? AND utilisateur_id = ? LIMIT 1");
    $stmt_statut->execute([$id_tache_a_cocher, $id_utilisateur_connecte]);
    $tache_actuelle = $stmt_statut->fetch();
    
    if ($tache_actuelle) {
        $statut_actuel = isset($tache_actuelle['statut']) ? (int)$tache_actuelle['statut'] : 0;
        
        // Calcul du cycle à 3 états (0 -> 1 -> 2 -> 0)
        $nouveau_statut = ($statut_actuel + 1) % 3;
        
        // 2. Persistance de la valeur brute via UPDATE préparé PDO doublement verrouillé
        $stmt_update = $lien->prepare("UPDATE todo_taches SET statut = ? WHERE id = ? AND utilisateur_id = ?");
        $stmt_update->execute([$nouveau_statut, $id_tache_a_cocher, $id_utilisateur_connecte]);
        
        $_SESSION['succes_projet'] = "Le niveau de maîtrise de la tâche a été mis à jour.";
    } else {
        $_SESSION['erreur_projet'] = "Accès refusé : Cette tâche ne vous appartient pas.";
    }
    
    header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
    exit();
}

// Action : Validation de suppression d'une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'suppression_tache_confirmee') {
    require_once __DIR__ . '/services/supprimer_taches.php';
    exit();
}

// Action : Sauvegarde rapide de la note textuelle d'une tâche
if ($action === 'sauvegarder_note' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/services/sauvegarder_note.php';
    exit();
}

// 7. --- GESTION DES MESSAGES DE NOTIFICATION FLUSH SESSION ---
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

// 8. --- PRÉPARATION DES DONNÉES DISPONIBLES POUR L'IHM (SERVICES DE LECTURE MULTI-USER) ---
$liste_tous_projets = getTousProjets($lien);
$list_categories = $projet_id ? getcategoriesParProjet($lien, $projet_id) : [];

$categories_selectionnees = (isset($_GET['categories_filtre']) && is_array($_GET['categories_filtre'])) ? $_GET['categories_filtre'] : [];
$recherche_mot_cle = isset($_GET['recherche_texte']) ? trim($_GET['recherche_texte']) : '';

$resultat = $projet_id ? getTachesParProjet($lien, $projet_id, $categories_selectionnees, $recherche_mot_cle) : null;

$tache_a_modifier = ((isset($_GET['id_tache']) || isset($_POST['id_tache'])) && ($action === 'modification_taches' || $action === 'modification_tache' || $action === 'suppression_tache')) ? getTachePourModification($lien, isset($_GET['id_tache']) ? (int)$_GET['id_tache'] : (int)$_POST['id_tache']) : null;

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
    <!-- Barre supérieure : Sélection et création de projet -->
    <div class="selector-box" style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
        <div class="selector-section" style="display: flex; align-items: center; gap: 10px;">
            
            <a href="index.php?projet_id=0&action=liste" 
   title="Réinitialiser les filtres et revenir à la liste complète" 
   style="display: inline-flex; align-items: center; justify-content: center; background: #34495e; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; height: 32px; box-sizing: border-box; transition: background 0.2s;"
   onmouseover="this.style.background='#2c3e50'" onmouseout="this.style.background='#34495e'">
   🏠 Accueil
</a>

            <label for="proj_select"><strong>Projet actif :</strong></label>
            <select id="proj_select" onchange="window.location.href='index.php?projet_id='+this.value;" style="padding: 5px; min-width: 200px; height: 32px;">
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

        <form action="index.php?action=ajout_projets" method="POST" class="creation-section-inline" style="margin: 0; display: flex; gap: 5px;">
            <input type="text" name="nom_projet" placeholder="Nom du nouveau projet..." required style="padding: 5px; width: 200px; height: 32px; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 4px;">
            <button type="submit" name="ajouter_projet" class="btn-orange" style="background: #e67e22; color: white; padding: 0 10px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; height: 32px;">+ Créer</button>
        </form>
    </div>

    <!-- Badge du projet actif et boutons d'action sensibles -->
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
                
                <!-- RÔLE SENSIBLE : Seul l'admin plateforme a le droit de détruire le projet -->
                <?php if (isset($_SESSION['mes_apps_cache']['todo']) && $_SESSION['mes_apps_cache']['todo'] === 'admin'): ?>
                    <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_projets" style="color: white; text-decoration: none; background: #c0392b; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;">🗑️ Supprimer le projet</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Raccourci d'ajout rapide de tâche -->
    <?php if ($projet_id > 0 && $action === 'liste'): ?>
        <div class="barre-outils-projet" style="margin-top: 15px; margin-bottom: 15px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px dashed #3498db;">
            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=ajout_taches" class="btn-blue" style="display: inline-block; background: #3498db; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">
                + Ajouter une tâche
            </a>
        </div>
    <?php endif; ?>

    <!-- Affichage des bandeaux sémantiques de succès / erreur -->
    <div class="zone-actions-ihm" style="margin-top: 20px;">
        <?php if (!empty($message_succes_projet)): ?>
            <div class="alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; border: 1px solid #c3e6cb; margin-bottom: 15px; font-weight: bold;">
                ✓ <?php echo htmlspecialchars($message_succes_projet, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($message_erreur_projet)): ?>
            <div class="alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; border: 1px solid #f5c6cb; margin-bottom: 15px; font-weight: bold;">
                ❌ <?php echo htmlspecialchars($message_erreur_projet, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- AIGUILLAGE DU CONTENU -->
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
                        Aucun projet actif. Veuillez en choisir un ou en créer un.
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
