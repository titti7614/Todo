
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon Central To-Do Multi-Projets</title>
    <link rel="stylesheet" type="text/css" href="lib/style.css">
</head>

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
                        <a href="../ihm/ajout_phase.php?projet_id=<?php echo $projet_id; ?>" class="btn-green" style="margin-bottom: 15px; display: inline-block;">+ Ajouter une phase</a>
                <!-- Formulaire dynamique : Ajouter une tâche -->
                        <a href="../ihm/ajout_tache.php?projet_id=<?php echo $projet_id; ?>" class="btn-blue" style="margin-bottom: 15px; display: inline-block;">+ Ajouter une tâche</a>
                <!-- Formulaire dynamique : Modifier une tâche -->
                 <a href="../ihm/modif_tache.php?projet_id=<?php echo $projet_id; ?>&id=<?php echo $tache_a_modifier['id']; ?>" class="btn-blue" style="margin-bottom: 15px; display: inline-block;">✏️ Modifier une tâche</a>
                 

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