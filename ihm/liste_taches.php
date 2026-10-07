<?php
// ihm/liste_taches.php
// Vue passive reçue de l'index. Elle consomme la variable de résultat $resultat.

// Sécurité pour la variable $projet_id si non définie
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;
?>

<!-- BLOC 3 : AFFICHAGE DES TÂCHES ASSOCIÉES -->
<h2>Liste des tâches</h2>
<div class="liste-taches">
   
    <?php if (!empty($resultat) && count($resultat) > 0): ?>
        <?php foreach ($resultat as $tache): ?>
            
            <div class="tache-item" style="display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #e2e8f0; background: #fff; margin-bottom: 8px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                
                <!-- LIGNE SUPÉRIEURE : INFOS ET ACTIONS -->
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div>
                        <!-- Badge de la catégorie avec sa couleur dynamique -->
                        <span class="badge-categories" style="background: <?php echo !empty($tache['couleur_categories']) ? $tache['couleur_categories'] : '#7f8c8d'; ?>; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; margin-right: 10px; font-weight: bold;">
                            <?php 
                            if (!empty($tache['nom_categories'])) {
                                echo htmlspecialchars($tache['nom_categories'], ENT_QUOTES, 'UTF-8');
                            } else {
                                echo 'Général';
                            }
                            ?>
                        </span>
                        
                        <!-- Libellé de la tâche -->
                        <span class="<?php echo ($tache['statut'] == 2) ? 'done' : ''; ?>" style="<?php echo ($tache['statut'] == 2) ? 'text-decoration: line-through; color: #a0aec0;' : ''; ?>">
                            <?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    
                    <!-- Zone des boutons d'action du CRUD -->
                    <div class="actions-tache" style="display: flex; gap: 8px; align-items: center;">
                        
                        <!-- Indicateur visuel du cycle à 3 états (🔴 -> 🟡 -> 🟢) -->
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                           title="Changer le statut de maîtrise" 
                           style="text-decoration: none; font-size: 1.1rem;">
                            <?php 
                            if ($tache['statut'] == 1) echo '🟡';
                            elseif ($tache['statut'] == 2) echo '🟢';
                            else echo '🔴';
                            ?>
                        </a>

                        <!-- Bouton Modifier (uniquement si la tâche n'est pas verte) -->
                        <?php if ($tache['statut'] != 2): ?>
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_taches&id_tache=<?php echo $tache['id']; ?>" 
                               class="btn-check" 
                               style="color: #3498db; text-decoration: none; font-weight: bold; font-size: 0.9rem;">
                               ✏️ Modifier
                            </a>
                        <?php endif; ?>
                        
                        <!-- Bouton Supprimer -->
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_tache&id_tache=<?php echo $tache['id']; ?>" 
                           class="btn-delete-task" 
                           title="Supprimer la tâche"
                           style="text-decoration: none;">
                           ❌
                        </a>
                    </div>
                </div>

                <!-- 🎯 LIGNE INFÉRIEURE : FORMULAIRE DE NOTE DE TEXTE AVEC ENREGISTRER + EFFACER QUICK-ACTION -->
                <div style="margin-top: 10px; width: 100%; background: #f8fafc; padding: 8px; border-radius: 4px; border-left: 3px solid #cbd5e1;">
                    <form action="index.php" method="POST" style="display: flex; gap: 8px; margin: 0; align-items: center; width: 100%;">
                        <input type="hidden" name="action" value="sauvegarder_note">
                        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
                        <input type="hidden" name="id_tache" value="<?php echo (int)$tache['id']; ?>">
                        
                        <!-- Input avec identifiant unique ciblé par le JS du bouton effacer -->
                        <input type="text" name="note_texte" id="input_note_<?php echo $tache['id']; ?>"
                               value="<?php echo isset($tache['note']) ? htmlspecialchars($tache['note'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                               placeholder="Ajouter une note ou remarque sur cette tâche..." 
                               style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; height: 28px; box-sizing: border-box;">
                        
                        <!-- Bouton Enregistrer -->
                        <button type="submit" title="Sauvegarder la note" style="background: #34495e; color: white; border: none; padding: 0 10px; border-radius: 4px; cursor: pointer; height: 28px; font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='#2c3e50'" onmouseout="this.style.background='#34495e'">
                            💾 Enregistrer
                        </button>

                        <!-- 🧼 NOUVEAU BOUTON BOUTON : Effacement automatique immédiat en 1 clic -->
                        <?php if (!empty($tache['note'])): ?>
                            <button type="button" title="Vider et supprimer la note" 
                                    onclick="document.getElementById('input_note_<?php echo $tache['id']; ?>').value = ''; this.form.submit();"
                                    style="background: #e74c3c; color: white; border: none; padding: 0 10px; border-radius: 4px; cursor: pointer; height: 28px; font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='#c0392b'" onmouseout="this.style.background='#e74c3c'">
                                🧼 Effacer
                            </button>
                        <?php endif; ?>
                    </form>
                </div>

            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">
            Aucune tâche enregistrée ou ne correspond aux filtres pour ce projet.
        </p>
    <?php endif; ?>
</div>
