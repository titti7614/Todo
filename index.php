<?php
// index.php — Contrôleur Principal Central Todo (Moitié 1/2 - Logique PHP)
define('VERSION_APP', '4.0.0'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// ======================================================================
// 🛠️ BLOC DE DÉVELOPPEMENT LOCAL COLLABORATIF DYNAMIQUE [V4.0.0]
// ======================================================================
// 1. Interception prioritaire du basculeur rapide d'URL (Axe rapide de test)
if (isset($_GET['switch_test_user'])) {
    $switch_id = (int)$_GET['switch_test_user'];
    if ($switch_id === 1) {
        $_SESSION['user_id'] = 1;
        $_SESSION['mes_apps_cache']['todo'] = 'admin';
    } elseif ($switch_id === 5) {
        $_SESSION['user_id'] = 5;
        $_SESSION['mes_apps_cache']['todo'] = 'user';
    } else {
        // Enregistre dynamiquement l'ID de n'importe quel autre utilisateur (ex: Cindy)
        $_SESSION['user_id'] = $switch_id;
        $_SESSION['mes_apps_cache']['todo'] = 'user';
    }
    // Redirection immédiate sur l'accueil pour vider l'URL et synchroniser le cycle
    header("Location: index.php?projet_id=0&action=liste");
    exit();
}

// 2. Initialisation par défaut si la session est vierge
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
    $_SESSION['mes_apps_cache']['todo'] = 'admin';
}
// ======================================================================

// 🛡️ BARRIÈRE DE SÉCURITÉ INTER-DOSSIERS STRICTE
if (!isset($_SESSION['user_id']) || !isset($_SESSION['mes_apps_cache']['todo'])) {
    header('Location: /portail/index.php', true, 302);
    exit();
}

// 🎯 CORRECTIF : Hydratation finale APRÈS l'interception de la bascule !
$id_utilisateur_connecte = (int)$_SESSION['user_id'];

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/conf/connexion_connect.php';
require_once __DIR__ . '/services/projet_services.php';

if (file_exists(__DIR__ . '/services/lister_categories.php')) {
    require_once __DIR__ . '/services/lister_categories.php';
}
if (file_exists(__DIR__ . '/services/lister_taches.php')) {
    require_once __DIR__ . '/services/lister_taches.php';
}

$lien = getTodoDatabaseConnection();

$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : (isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0);
$action    = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'liste'); 

$uniquement_les_miennes = isset($_GET['filtre_moi']) && (int)$_GET['filtre_moi'] === 1;
$nom_fichier_actuel = basename($_SERVER['PHP_SELF']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'ajout_projets') { require_once __DIR__ . '/services/ajouter_projets.php'; exit(); }
    if ($action === 'modification_projets') { require_once __DIR__ . '/services/modifier_projets.php'; exit(); }
    if ($action === 'suppression_projets_valide') { require_once __DIR__ . '/services/supprimer_projets.php'; exit(); }
    if ($action === 'ajout_categories') { require_once __DIR__ . '/services/ajouter_categories.php'; exit(); }
    if ($action === 'ajout_taches') { require_once __DIR__ . '/services/ajouter_taches.php'; exit(); }
    if ($action === 'suppression_categories_confirmee') { require_once __DIR__ . '/services/supprimer_categories.php'; exit(); }
    if ($action === 'suppression_tache_confirmee') { require_once __DIR__ . '/services/supprimer_taches.php'; exit(); }
    if ($action === 'sauvegarder_note') { require_once __DIR__ . '/services/sauvegarder_note.php'; exit(); }
    if ($action === 'modification_taches' || $action === 'modification_tache') { require_once __DIR__ . '/services/modifier_taches.php'; exit(); }
    if ($action === 'modification_categories' && isset($_POST['nom_categories'])) { require_once __DIR__ . '/services/modifier_categories.php'; exit(); }

    if ($action === 'marquer_projet_lu') {
        $projet_id_lu = isset($_POST['projet_id_lu']) ? (int)$_POST['projet_id_lu'] : 0;
        if ($projet_id_lu > 0 && function_exists('marquerProjetCommeLu')) {
            marquerProjetCommeLu($lien, $id_utilisateur_connecte, $projet_id_lu);
        }
        exit(); 
    }
}

if (($action === 'cocher_tache' || $action === 'cocher') && isset($_GET['id_tache'])) {
    $id_tache_a_cocher = (int)$_GET['id_tache'];
    $stmt_statut = $lien->prepare("SELECT statut FROM todo_taches WHERE id = ? LIMIT 1");
    $stmt_statut->execute([$id_tache_a_cocher]);
    $tache_actuelle = $stmt_statut->fetch();
    
    if ($tache_actuelle) {
        $statut_actuel = isset($tache_actuelle['statut']) ? (int)$tache_actuelle['statut'] : 0;
        $nouveau_statut = ($statut_actuel + 1) % 3;
        $stmt_update = $lien->prepare("UPDATE todo_taches SET statut = ? WHERE id = ?");
        $stmt_update->execute([$nouveau_statut, $id_tache_a_cocher]);
        $_SESSION['succes_projet'] = "Le niveau de maîtrise de la tâche a été mis à jour.";
    }
    header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
    exit();
}

$message_erreur_projet = $_SESSION['erreur_projet'] ?? null; unset($_SESSION['erreur_projet']);
$message_succes_projet = $_SESSION['succes_projet'] ?? null; unset($_SESSION['succes_projet']);
$doublon_id = isset($_GET['dup_id']) ? (int)$_GET['dup_id'] : null;
$doublon_nom = isset($_GET['dup_nom']) ? trim($_GET['dup_nom']) : "";

$liste_tous_projets = getTousProjets($lien);
$list_categories = $projet_id ? getcategoriesParProjet($lien, $projet_id) : [];
// 👤 Récupération de la liste des utilisateurs de la plateforme
$list_utilisateurs = $lien->query("SELECT id, name AS nom FROM portail_users ORDER BY name ASC")->fetchAll();

// 🎯 VERROU COLLABORATIF V4.0.0 : Si un projet est actif, on restreint l'attribution aux seuls membres du cercle !
if ($projet_id > 0 && function_exists('getParticipantsParProjet')) {
    $list_utilisateurs = getParticipantsParProjet($lien, $projet_id);
}

// Extraction du nom de l'utilisateur connecté pour le badge supérieur
$nom_user_connecte = "Visiteur";
if (!empty($list_utilisateurs)) {
    foreach ($list_utilisateurs as $u) {
        if ($u['id'] == $id_utilisateur_connecte) { $nom_user_connecte = $u['nom']; break; }
    }
}


$categories_selectionnees = (isset($_GET['categories_filtre']) && is_array($_GET['categories_filtre'])) ? $_GET['categories_filtre'] : [];
$recherche_mot_cle = isset($_GET['recherche_texte']) ? trim($_GET['recherche_texte']) : '';

$nouveaux_projets_partages = ($projet_id === 0 && function_exists('getNouveauxProjetsPartages')) ? getNouveauxProjetsPartages($lien, $id_utilisateur_connecte) : [];
$historique_projets_partages = function_exists('getHistoriqueProjetsPartages') ? getHistoriqueProjetsPartages($lien, $id_utilisateur_connecte) : [];
// 👥 Nouveauté V4.0.0 : Récupération des cercles de confiance de l'utilisateur pour le formulaire de création
$mes_groupes_autorises = getGroupesUtilisateur($lien, $id_utilisateur_connecte);
$resultat = $projet_id ? getTachesParProjet($lien, $projet_id, $categories_selectionnees, $recherche_mot_cle, $uniquement_les_miennes) : null;
$tache_a_modifier = ((isset($_GET['id_tache']) || isset($_POST['id_tache'])) && ($action === 'modification_taches' || $action === 'modification_tache' || $action === 'suppression_tache')) ? getTachePourModification($lien, isset($_GET['id_tache']) ? (int)$_GET['id_tache'] : (int)$_POST['id_tache']) : null;
$tache = $tache_a_modifier;
// 👤 Nouveauté V4.0.0 : Extraction des participants du cercle si une tâche d'équipe est en cours de modification
$participants_projet_courant = [];
if ($projet_id > 0 && ($action === 'modification_taches' || $action === 'modification_tache')) {
    $participants_projet_courant = getParticipantsParProjet($lien, $projet_id);
}


$categories_id_contexte = isset($_REQUEST['categories_id']) ? (int)$_REQUEST['categories_id'] : 0;
$categories_a_modifier = (($action === 'modification_categories' || $action === 'suppression_categories') && $categories_id_contexte > 0 && function_exists('getcategoriesPourModification')) ? getcategoriesPourModification($lien, $categories_id_contexte) : null;

$projet_a_supprimer = ($action === 'suppression_projets' && $projet_id > 0) ? getProjetParId($lien, $projet_id) : null;
$projet_a_modifier = ($action === 'modification_projets' && $projet_id > 0) ? getProjetParId($lien, $projet_id) : null;
?>

<!-- index.php — Contrôleur Principal Central Todo (Partie 4/4 - Morceau A - En-tête Épuré & Basculeur) -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Central To-Do Multi-Projets</title>
    <link rel="stylesheet" type="text/css" href="/lib/style.css">
</head>
<body>
<div class="container" style="position: relative;">

    <!-- 👤 BANDEAU DE CONTRÔLE COLLABORATIF ÉPURÉ V4.0.0 -->
    <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); gap: 15px; flex-wrap: wrap;">
        
        <!-- ZONE GAUCHE : 🎯 Le Point X Historique stable -->
        <div style="display: flex; align-items: center;">
            <?php if (!empty($historique_projets_partages)): ?>
                <div class="dropdown-point-x" style="position: relative; display: inline-block;">
                    <button type="button" style="background: #2563eb; color: white; padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; height: 32px; box-sizing: border-box; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">
                        📋 Projets Partagés (<?php echo count($historique_projets_partages); ?>) <span style="font-size: 0.65rem;">▼</span>
                    </button>
                    <div class="dropdown-content-point-x" style="display: none; position: absolute; left: 0; background-color: #ffffff; min-width: 280px; box-shadow: 0px 8px 24px rgba(149, 157, 165, 0.2); border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-top: 6px; z-index: 9999;">
                        <span style="display: block; font-size: 0.7rem; color: #94a3b8; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 8px;">Vos accès d'équipe :</span>
                        <div style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px;">
                            <?php foreach ($historique_projets_partages as $hp): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 8px; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: #1e293b; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        📁 <?php echo htmlspecialchars($hp['nom_projet'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <a href="index.php?projet_id=<?php echo $hp['id']; ?>&action=liste" style="background: #f1f5f9; color: #1e293b; text-decoration: none; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; border: 1px solid #cbd5e1; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                        Consulter
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <style>
                    .dropdown-content-point-x { display: none; }
                    .dropdown-content-point-x div::-webkit-scrollbar { width: 4px; }
                    .dropdown-content-point-x div::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
                </style>
            <?php else: ?>
                <span style="font-size: 0.8rem; color: #94a3b8; font-style: italic; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px dashed #e2e8f0; height: 32px; display: inline-flex; align-items: center; box-sizing: border-box;">🔒 Aucun projet partagé</span>
            <?php endif; ?>
        </div>

        <!-- ZONE DROITE : 👤 Identité session interactive & Version Applicative -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; padding: 4px 12px; border-radius: 6px; border: 1px solid #e2e8f0; height: 32px; box-sizing: border-box;">
                <span style="font-size: 0.75rem; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Test Profil :</span>
                
                <select id="switch_test_user_select" style="border: none; background: transparent; font-size: 0.85rem; font-weight: 700; color: #1e293b; cursor: pointer; padding-right: 5px; font-family: inherit;">
                    <option value="1" <?php echo ($id_utilisateur_connecte === 1) ? 'selected' : ''; ?>>👑 Will (Admin)</option>
                    <option value="5" <?php echo ($id_utilisateur_connecte === 5) ? 'selected' : ''; ?>>👥 BSI (Équipe)</option>
                    <?php if (!empty($list_utilisateurs)): ?>
                        <?php foreach ($list_utilisateurs as $u): ?>
                            <?php if ($u['id'] != 1 && $u['id'] != 5): ?>
                                <option value="<?php echo (int)$u['id']; ?>" <?php echo ($id_utilisateur_connecte == $u['id']) ? 'selected' : ''; ?>>
                                    👤 <?php echo htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                
                <span style="color: #cbd5e1; margin: 0 2px;">|</span>
                <span style="background: <?php echo ($_SESSION['mes_apps_cache']['todo'] === 'admin') ? '#34495e' : '#64748b'; ?>; color: #ffffff; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px;">
                    <?php echo strtoupper(htmlspecialchars($_SESSION['mes_apps_cache']['todo'], ENT_QUOTES, 'UTF-8')); ?>
                </span>
            </div>
            
            <div style="background: #e8f5e9; color: #1e7e34; border: 1px solid #c3e6cb; padding: 0 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; height: 32px; display: inline-flex; align-items: center; box-sizing: border-box;">
                v<?php echo VERSION_APP; ?>
            </div>
        </div>

    </div>
    
    <h1>📋 Central To-Do Multi-Projets</h1>
<!-- index.php — Contrôleur Principal Central Todo (Partie 4/4 - Morceau B - Sélections & Actions Hautes) -->
    <!-- Barre supérieure : Sélection et création de projet -->
    <div class="selector-box" style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
        <div class="selector-section" style="display: flex; align-items: center; gap: 10px;">
           <a href="index.php" class="btn-accueil" style="display: inline-flex; align-items: center; justify-content: center; background: #34495e; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; height: 32px; box-sizing: border-box;">🏠 Accueil</a>
           <label for="proj_select"><strong>Projet actif :</strong></label>
            <select id="proj_select" onchange="window.location.href='index.php?projet_id='+this.value;" style="padding: 5px; min-width: 230px; height: 32px; font-family: inherit;">
                <option value="0">-- Choisir un projet --</option>
                <?php if (!empty($liste_tous_projets)): ?>
                    <?php foreach ($liste_tous_projets as $p): ?>
                        <?php $icone_projet = (!empty($p['groupe_id']) && (int)$p['groupe_id'] > 0) ? '👥 ' : '🔒 '; ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $projet_id) ? 'selected' : ''; ?>><?php echo $icone_projet . htmlspecialchars($p['nom_projet'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <!-- Formulaire de création de projet intelligent V4.0.0 avec détection d'index -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <form action="index.php?action=ajout_projets" method="POST" style="margin: 0; display: flex; gap: 5px; align-items: center;">
                <input type="text" name="nom_projet" placeholder="Nom du nouveau projet..." required style="padding: 5px; width: 200px; height: 32px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                
                <?php if (!empty($mes_groupes_autorises) && count($mes_groupes_autorises) > 1): ?>
                    <!-- Sélecteur pour profil hybride -->
                    <select name="groupe_id_selectionne" style="padding: 5px; height: 32px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-size: 0.85rem; font-weight: 500;" required>
                        <option value="" disabled selected>🔒 Privé (Solo)</option>
                        <?php foreach ($mes_groupes_autorises as $groupe): ?>
                            <option value="<?php echo (int)$groupe['id']; ?>">👥 Groupe <?php echo htmlspecialchars($groupe['nom_groupe'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif (!empty($mes_groupes_autorises) && count($mes_groupes_autorises) === 1): ?>
                    <!-- Case à cocher explicite si l'utilisateur n'a qu'un seul cercle -->
                    <?php $unique_groupe = $mes_groupes_autorises[0]; ?>
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: #475569; background: #fff; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 4px; height: 32px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="groupe_id_selectionne" value="<?php echo (int)$unique_groupe['id']; ?>" style="cursor: pointer; width: 15px; height: 16px; margin: 0;">
                        <span>📢 Partager avec : <strong style="color: #2563eb;"><?php echo htmlspecialchars($unique_groupe['collegues'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    </label>
                <?php endif; ?>

                <button type="submit" class="btn-orange" style="background: #e67e22; color: white; padding: 0 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; height: 32px; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box;">+ Créer</button>
            </form>
        </div>
    </div>

    <!-- Badge du projet actif & Boutons d'administration sensibles -->
    <?php if ($projet_id > 0): ?>
        <?php
        $nom_projet_courant = "Projet en cours";
        foreach ($liste_tous_projets as $p) { if ($p['id'] == $projet_id) { $nom_projet_courant = $p['nom_projet']; break; } }
        ?>
        <div class="project-badge-top" style="display: flex; justify-content: space-between; align-items: center; background: #e67e22; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; color: white;">
            <span style="font-weight: bold;">📁 Projet actif : <?php echo htmlspecialchars($nom_projet_courant, ENT_QUOTES, 'UTF-8'); ?></span>
            <div>
                <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_projets" style="color: white; text-decoration: none; background: #3498db; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; margin-right: 5px;">✏️ Renommer / Partager</a>
                <?php if (isset($_SESSION['mes_apps_cache']['todo']) && $_SESSION['mes_apps_cache']['todo'] === 'admin'): ?>
                    <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_projets" style="color: white; text-decoration: none; background: #c0392b; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;">🗑️ Supprimer le projet</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<!-- index.php — Contrôleur Principal Central Todo (Partie 4/4 - Morceau C - Routage Vues & JavaScript Natif) -->
    <?php if ($projet_id > 0 && $action === 'liste'): ?>
        <div style="margin-bottom: 15px; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px dashed #3498db;">
            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=ajout_taches" style="background: #3498db; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">+ Ajouter une tâche</a>
        </div>
    <?php endif; ?>

    <div class="zone-actions-ihm" style="margin-top: 20px;">
        <?php if (!empty($message_succes_projet)): ?><div class="alert-success" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">✓ <?php echo htmlspecialchars($message_succes_projet, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <?php if (!empty($message_erreur_projet)): ?><div class="alert-danger" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">❌ <?php echo htmlspecialchars($message_erreur_projet, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

        <!-- 🔔 IHM Graphique du Bandeau d'Accueil Asynchrone -->
        <?php if ($projet_id === 0 && !empty($nouveaux_projets_partages)): ?>
            <?php foreach ($nouveaux_projets_partages as $projet_partage): ?>
                <div id="bandeau_notification_<?php echo $projet_partage['id']; ?>" class="alert-info" style="background-color: #ebf8ff; color: #1e3a8a; padding: 15px; border-radius: 4px; border: 1px solid #bfdbfe; margin-bottom: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center; gap: 15px; transition: all 0.4s ease-in-out;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>📢</span>
                        <!-- 📢 Affichage explicite du créateur original du projet d'équipe -->
                        <span>📢 Nouveau projet partagé par <strong style="color: #2563eb;"><?php echo htmlspecialchars($projet_partage['nom_createur'], ENT_QUOTES, 'UTF-8'); ?></strong> : <strong style="color: #1d4ed8;"><?php echo htmlspecialchars($projet_partage['nom_projet'], ENT_QUOTES, 'UTF-8'); ?></strong> (<?php echo (int)$projet_partage['nb_taches']; ?> tâches).</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a href="index.php?projet_id=<?php echo $projet_partage['id']; ?>&action=liste" style="background: #3b82f6; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">👉 Consulter</a>
                        <button type="button" onclick="acquitterProjetPartage(<?php echo $projet_partage['id']; ?>)" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">✓ OK, j'ai vu</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php
        // AIGUILLAGE DU CONTENU GRAPHIQUE EN FONCTION DE L'ACTION
        switch ($action) {
            case 'ajout_projets': include __DIR__ . '/ihm/ajout_projets.php'; break;
            case 'modification_projets': include __DIR__ . '/ihm/modification_projets.php'; break;
            case 'suppression_projets': include __DIR__ . '/ihm/suppression_projets.php'; break;
            case 'liste_categories': include __DIR__ . '/ihm/liste_categories.php'; break;
            case 'ajout_categories': include __DIR__ . '/ihm/ajout_categories.php'; break;
            case 'modification_categories': include __DIR__ . '/ihm/modification_categories.php'; break;
            case 'suppression_categories': include __DIR__ . '/ihm/suppression_categories.php'; break;
            case 'ajout_taches': include __DIR__ . '/ihm/ajout_taches.php'; break;
            case 'modification_taches': include __DIR__ . '/ihm/modification_taches.php'; break;
            case 'suppression_tache': include __DIR__ . '/ihm/suppression_taches.php'; break;
            case 'liste':
            default:
                if ($projet_id > 0) {
                    include __DIR__ . '/ihm/liste_categories.php';
                    include __DIR__ . '/ihm/liste_taches.php';
                } else {
                    echo '<p style="text-align: center; color: #7f8c8d; font-style: italic; padding: 20px;">Aucun projet actif. Veuillez en choisir un ou en créer un.</p>';
                }
                break;
        }
        ?>
    </div>
</div>

<script>
function acquitterProjetPartage(idProjet) {
    const desDonnees = new FormData();
    desDonnees.append('action', 'marquer_projet_lu');
    desDonnees.append('projet_id_lu', idProjet);

    fetch('index.php', {
        method: 'POST',
        body: desDonnees
    })
    .then(function(reponse) {
        if (reponse.ok) {
            var leBandeau = document.getElementById('bandeau_notification_' + idProjet);
            if (leBandeau) {
                leBandeau.style.opacity = '0';
                leBandeau.style.transform = 'translateY(-10px)';
                setTimeout(function() { leBandeau.remove(); }, 400);
            }
            window.location.reload();
        }
    })
    .catch(function(erreur) {
        console.error("Erreur technique Fetch :", erreur);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Écouteur stable pour le Point X
    const boutonPointX = document.querySelector('.dropdown-point-x button');
    const contenuPointX = document.querySelector('.dropdown-content-point-x');

    if (boutonPointX && contenuPointX) {
        boutonPointX.addEventListener('click', function(e) {
            e.stopPropagation();
            const estAffiche = contenuPointX.style.display === 'block';
            contenuPointX.style.display = estAffiche ? 'none' : 'block';
        });
        contenuPointX.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        document.addEventListener('click', function() {
            contenuPointX.style.display = 'none';
        });
    }

    // 2. Écouteur natif pour forcer la redirection et le rechargement immédiat du basculeur
    var selectUser = document.getElementById('switch_test_user_select');
    if (selectUser) {
        selectUser.addEventListener('change', function() {
            window.location.href = 'index.php?switch_test_user=' + this.value;
        });
    }
});
</script>
</body>
</html>
