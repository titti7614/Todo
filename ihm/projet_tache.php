<?php
// 1. Appel du fichier de service (ajustez le chemin si nécessaire)
require_once __DIR__ . '/../services/projet_tache.php'; 


// 2. Récupération de la liste des projets via la fonction du service
// (Assurez-vous que votre variable de connexion BDD, ici nommée $lien, existe avant cette ligne)
$liste_tous_taches = getTachesParProjet($lien, $projet_id); 

// 3. Sécurité pour la variable $projet_id (si elle n'est pas définie dans l'URL)
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;

exit;
?>


<!-- BLOC 3 : AFFICHAGE DES TÂCHES ASSOCIÉES -->
            <h2>Liste des tâches</h2>
            <div class="liste-taches">
               
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
                    <!-- <?php //endwhile; ?>
                <?php //else: ?> -->
                    <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">
                        Aucune tâche enregistrée pour ce projet.
                    </p>
                <?php //endif; ?>
            </div>