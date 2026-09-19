<?php
// ihm/liste_phases.php
// Reçoit de l'index : $list_phases, $projet_id et la variable $phases_selectionnees (tableau)
?>
<div class="zone-phases" style="margin-top: 20px; background: #f8fafc; padding: 20px; border-radius: 6px; border: 1px solid #e2e8f0;">
    
    <form action="index.php" method="GET" id="form_filtre_multi" style="margin: 0;">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
        <input type="hidden" name="action" value="liste">

        <p style="margin-top: 0; margin-bottom: 12px; font-weight: bold; color: #1e293b; font-size: 0.95rem;">
            📅 Filtrer l'affichage par phase :
        </p>
        
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px;">
            
            <!-- GAUCHE : Les cases à cocher avec boutons de modification/suppression intégrés -->
            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" 
                   style="display: inline-block; padding: 6px 12px; background: <?php echo empty($phases_selectionnees) ? '#3498db' : '#cbd5e1'; ?>; color: <?php echo empty($phases_selectionnees) ? 'white' : '#475569'; ?>; text-decoration: none; border-radius: 4px; font-size: 0.85rem; font-weight: bold; transition: all 0.2s;">
                   🌐 Tout afficher
                </a>

                <?php if (!empty($list_phases)): ?>
                    <?php foreach ($list_phases as $phase): ?>
                        <?php $est_coche = in_array($phase['id'], $phases_selectionnees); ?>
                        
                        <!-- Conteneur de la pastille de phase -->
                        <div style="display: inline-flex; align-items: center; background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid <?php echo $est_coche ? '#3498db' : '#cbd5e1'; ?>; font-size: 0.85rem; font-weight: 500; transition: all 0.15s; box-shadow: <?php echo $est_coche ? '0 0 0 1px #3498db' : 'none'; ?>; gap: 8px;">
                            
                            <!-- Case à cocher pour le filtre -->
                            <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="phases_filtre[]" value="<?php echo (int)$phase['id']; ?>" 
                                       <?php echo $est_coche ? 'checked' : ''; ?>
                                       onchange="document.getElementById('form_filtre_multi').submit();"
                                       style="cursor: pointer; accent-color: #3498db; margin: 0;">
                                <span style="color: #334155;"><?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </label>

                            <!-- Séparateur discret -->
                            <span style="color: #e2e8f0; font-size: 0.8rem;">|</span>

                            <!-- Petit bouton Modifier (Redirige vers l'IHM de modif en POST via un clic simulé ou GET propre) -->
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_phases&phase_id=<?php echo $phase['id']; ?>" 
                               title="Modifier cette phase" 
                               style="text-decoration: none; font-size: 0.75rem; cursor: pointer; filter: grayscale(100%); transition: transform 0.1s;"
                               onmouseover="this.style.filter='none'; this.style.transform='scale(1.2)'" 
                               onmouseout="this.style.filter='grayscale(100%)'; this.style.transform='scale(1)'">
                               ✏️
                            </a>
                           <!-- Bouton de suppression qui envoie maintenant vers le bel écran d'avertissement graphique -->
<a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_phase&phase_id=<?php echo $phase['id']; ?>" 
   title="Supprimer cette phase" 
   style="text-decoration: none; font-size: 0.75rem; cursor: pointer; filter: grayscale(100%); transition: transform 0.1s;"
   onmouseover="this.style.filter='none'; this.style.transform='scale(1.2)'" 
   onmouseout="this.style.filter='grayscale(100%)'; this.style.transform='scale(1)'">
   ❌
</a>
</div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- DROITE : Bouton d'ajout vert -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=ajout_phases" 
                   title="Créer une nouvelle phase" 
                   style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 1.2rem;">
                   +
                </a>
            </div>
            
        </div>
    </form>
</div>
