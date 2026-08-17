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

// 2. TRAITEMENT : Si l'utilisateur ajoute une tâche
if (isset($_POST['ajouter_tache']) && !empty(trim($_POST['texte_tache'])) && $projet_id > 0) {
    $texte = mysqli_real_escape_string($lien, trim($_POST['texte_tache']));
    $phase = mysqli_real_escape_string($lien, $_POST['phase_tache']);

    $sql_ajout = "INSERT INTO todo_list (projet_id, texte, phase) VALUES ($projet_id, '$texte', '$phase')";
    mysqli_query($lien, $sql_ajout);
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

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon Central To-Do Multi-Projets</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            color: #2c3e50;
            margin-top: 0;
        }

        .project-badge-top {
            display: inline-block;
            background: #e67e22;
            color: #fff;
            padding: 5px 10px;
            font-size: 0.85rem;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: bold;
            width: 100%;
            box-sizing: border-box;
        }

        .selector-box {
            background: #2c3e50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .selector-section {
            display: flex;
            align-items: center;
        }

        .selector-box select {
            padding: 5px;
            border-radius: 4px;
            margin-left: 10px;
        }

        .creation-section-inline {
            display: flex;
            gap: 5px;
        }

        .creation-section-inline input[type="text"] {
            padding: 5px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 0.85rem;
            color: #333;
        }

        .zone-formulaires {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .bloc-form {
            background: #eef2f7;
            padding: 15px;
            border-radius: 5px;
        }

        .form-ligne {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 10px;
        }

        input[type="text"],
        select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            color: white;
        }

        .btn-green { background: #27ae60; }
        .btn-green:hover { background: #219653; }
        .btn-blue { background: #3498db; }
        .btn-blue:hover { background: #2980b9; }
        .btn-orange { background: #e67e22; padding: 5px 10px; font-size: 0.85rem; }
        .btn-orange:hover { background: #d35400; }

        .tache-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .tache-item:last-child { border-bottom: none; }
        .done { color: #95a5a6; text-decoration: line-through; font-style: italic; }

        .badge-phase {
            background: #3498db;
            color: white;
            padding: 2px 6px;
            font-size: 0.8rem;
            border-radius: 4px;
            margin-right: 10px;
            font-weight: bold;
            display: inline-block;
            min-width: 120px;
            text-align: center;
        }

        .btn-check {
            text-decoration: none;
            color: #27ae60;
            font-weight: bold;
            border: 1px solid #27ae60;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .btn-check:hover {
            background: #27ae60;
            color: white;
        }
        /* Bouton de suppression de tâche individuelle */
        .btn-delete-task {
            text-decoration: none;
            color: #e74c3c;
            font-weight: bold;
            border: 1px solid #e74c3c;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        .btn-delete-task:hover {
            background: #e74c3c;
            color: white;
        }

        /* Conteneur d'actions pour aligner les boutons horizontalement */
        .actions-tache {
            display: flex;
            gap: 8px;
            align-items: center;
        }

    </style>

<body>
    <div class="container">
        <h1>📋 Central To-Do Multi-Projets</h1>

        <!-- Barre globale : Choix du projet ET Création d'un nouveau projet -->
        <div class="selector-box">
            <div class="selector-section">
                <label for="proj_select"><strong>Projet actif :</strong></label>
                <select id="proj_select" onchange="window.location.href='<?php echo $nom_fichier_actuel; ?>?projet_id='+this.value;">
                    <option value="0">-- Choisir un projet --</option>
                    <?php while ($p = mysqli_fetch_assoc($liste_tous_projets)): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $projet_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nom_projet']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Formulaire inline pour créer un nouveau projet rapidement -->
            <form action="<?php echo $nom_fichier_actuel; ?>" method="POST" class="creation-section-inline">
                <input type="text" name="nom_projet" placeholder="Nom du nouveau projet..." required>
                <button type="submit" name="ajouter_projet" class="btn-orange">+ Créer</button>
            </form>
        </div>

        <!-- BLOC 1 : Message d'attente (masqué si projet actif) -->
        <div class="msg-attente" style="<?php echo ($projet_id > 0) ? 'display: none;' : ''; ?>">
            <p style="text-align: center; color: #7f8c8d; font-style: italic; padding: 30px 0;">
                Veuillez sélectionner un projet ou en créer un nouveau pour commencer à gérer vos tâches.
            </p>
        </div>

        <!-- BLOC 2 : Interface principale (masquée si aucun projet actif) -->
        <div class="interface-principale" style="<?php echo ($projet_id == 0) ? 'display: none;' : ''; ?>">
            
            <div class="project-badge-top" style="display: flex; justify-content: space-between; align-items: center; background: #e67e22; padding: 5px 15px;">
                <span>Projet actif : <?php echo htmlspecialchars($nom_projet_courant); ?></span>
                <a href="?projet_id=<?php echo $projet_id; ?>&action=supprimer_projet" 
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer TOUT ce projet (ainsi que ses phases et ses tâches) ?');" 
                   style="color: white; text-decoration: none; background: #c0392b; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; margin-left: 15px;">
                   🗑️ Supprimer le projet
                </a>
            </div>

            <div class="zone-formulaires">
                <!-- Formulaire pour ajouter une nouvelle phase -->
                <div class="bloc-form">
                    <h3>Ajouter une nouvelle phase</h3>
                    <form action="<?php echo $nom_fichier_actuel; ?>" method="POST">
                        <input type="hidden" name="projet_id" value="<?php echo $projet_id; ?>">
                        <div class="form-ligne">
                            <label for="nom_phase">Nom de la phase :</label>
                            <input type="text" name="nom_phase" id="nom_phase" placeholder="Ex: Planification" required>
                        </div>
                        <button type="submit" name="ajouter_phase" class="btn-green">+ Ajouter Phase</button>
                    </form>
                </div>

                <!-- Formulaire dynamique : Ajouter OU Modifier une tâche -->
                <div class="bloc-form">
                    <h3><?php echo $tache_a_modifier ? "Modifier la tâche" : "Ajouter une nouvelle tâche"; ?></h3>
                    <form action="<?php echo $nom_fichier_actuel; ?>" method="POST">
                        <input type="hidden" name="projet_id" value="<?php echo $projet_id; ?>">
                        
                        <?php if ($tache_a_modifier): ?>
                            <input type="hidden" name="id_tache" value="<?php echo $tache_a_modifier['id']; ?>">
                        <?php endif; ?>

                        <div class="form-ligne">
                            <label for="texte_tache">Texte de la tâche :</label>
                            <input type="text" name="texte_tache" id="texte_tache" 
                                   value="<?php echo $tache_a_modifier ? htmlspecialchars($tache_a_modifier['texte']) : ''; ?>" 
                                   placeholder="Ex: Préparer le rapport..." required>
                        </div>
                        
                        <div class="form-ligne">
                            <label for="phase_tache">Phase :</label>
                            <select name="phase_tache" id="phase_tache" required>
                                <option value="Général">-- Sans Phase --</option>
                                <?php if ($list_phases && mysqli_num_rows($list_phases) > 0): ?>
                                    <?php while ($phase = mysqli_fetch_assoc($list_phases)): ?>
                                        <option value="<?php echo htmlspecialchars($phase['nom']); ?>" 
                                            <?php echo ($tache_a_modifier && $tache_a_modifier['phase'] == $phase['nom']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($phase['nom']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php if ($tache_a_modifier): ?>
                            <button type="submit" name="modifier_tache" class="btn-blue">Enregistrer les modifications</button>
                            <a href="<?php echo $nom_fichier_actuel; ?>?projet_id=<?php echo $projet_id; ?>" style="display:inline-block; margin-top:10px; color:#7f8c8d; font-size:0.9rem; text-decoration:none; margin-left: 10px;">Annuler</a>
                        <?php else: ?>
                            <button type="submit" name="ajouter_tache" class="btn-blue">+ Ajouter Tâche</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- BLOC 3 : AFFICHAGE DES TÂCHES ASSOCIÉES -->
            <h2>Liste des tâches</h2>
            <div class="liste-taches">
                <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                    <?php while ($tache = mysqli_fetch_assoc($resultat)): ?>
                        <div class="tache-item">
                            <div>
                                <span class="badge-phase">
                                    <?php 
                                    if (!empty($tache['phase']) && !is_numeric($tache['phase'])) {
                                        echo htmlspecialchars($tache['phase']);
                                    } elseif (isset($tab_phases[$tache['phase']])) {
                                        echo htmlspecialchars($tab_phases[$tache['phase']]);
                                    } else {
                                        echo 'Général';
                                    }
                                    ?>
                                </span>
                                <span class="<?php echo ($tache['statut'] == 1) ? 'done' : ''; ?>">
                                    <?php echo htmlspecialchars($tache['texte']); ?>
                                </span>
                            </div>
                            
                            <!-- Zone des boutons d'action du CRUD -->
                            <div class="actions-tache" style="display: flex; gap: 8px;">
                                <?php if ($tache['statut'] == 0): ?>
                                    <a href="?projet_id=<?php echo $projet_id; ?>&action=modifier&id=<?php echo $tache['id']; ?>" class="btn-check" style="color: #3498db; border-color: #3498db;">✏️ Modifier</a>
                                    <a href="?projet_id=<?php echo $projet_id; ?>&action=cocher&id=<?php echo $tache['id']; ?>" class="btn-check">✓ Fait</a>
                                <?php endif; ?>
                                
                                <!-- BOUTON DE SUPPRESSION DE LA TÂCHE -->
                                <a href="?projet_id=<?php echo $projet_id; ?>&action=supprimer_tache&id=<?php echo $tache['id']; ?>" 
                                   class="btn-delete-task" 
                                   onclick="return confirm('Voulez-vous vraiment supprimer définitivement cette tâche ?');"
                                   title="Supprimer la tâche">
                                   ❌
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">
                        Aucune tâche enregistrée pour ce projet.
                    </p>
                <?php endif; ?>
            </div>
        </div> <!-- Fin de l'interface principale -->
    </div> <!-- Fin du container -->
</body>
</html>

            <!-- Fin de la zone de formulaires -->
