<?php
// ihm/modification_phases.php
// Vue passive. Reçoit de l'index : $phase_a_modifier et $projet_id

if (!$phase_a_modifier):
?>
    <div class="alert-danger" style="background:#f8d7da; color:#721c24; padding:15px; border-radius:4px; font-weight:bold;">
        ⚠️ Erreur : La phase demandée est introuvable.
    </div>
    <p><a href="index.php?projet_id=<?php echo $projet_id; ?>&action=liste" class="btn-blue">Retour</a></p>
<?php 
else: 
?>
<div class="zone-formulaires" style="margin-top: 20px;">
    <div class="bloc-form" style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
        <h3>✏️ Modifier la phase</h3>
        
        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="modification_phases">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
            <input type="hidden" name="phase_id" value="<?php echo (int)$phase_a_modifier['id']; ?>">

            <!-- Champ 1 : Nom de la phase -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="nom_phase" style="display:block; font-weight:bold; margin-bottom:5px;">Nom de la phase :</label>
                <input type="text" name="nom_phase" id="nom_phase" 
                       value="<?php echo htmlspecialchars($phase_a_modifier['nom'], ENT_QUOTES, 'UTF-8'); ?>" 
                       required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>

            <!-- 🎯 Champ 2 : Modification de la couleur existante -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="couleur_phase" style="display:block; font-weight:bold; margin-bottom:5px;">Couleur de la phase :</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="color" name="couleur_phase" id="couleur_phase" 
                           value="<?php echo !empty($phase_a_modifier['couleur']) ? htmlspecialchars($phase_a_modifier['couleur'], ENT_QUOTES, 'UTF-8') : '#e67e22'; ?>" 
                           style="width: 50px; height: 40px; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; padding: 0;">
                    <span style="font-size: 0.9rem; color: #64748b;">Cliquez sur le carré pour changer la couleur</span>
                </div>
            </div>

            <!-- Boutons de validation -->
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" style="background: #3498db; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Enregistrer les modifications</button>
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display:inline-block; padding: 8px 16px; background:#7f8c8d; color:white; border-radius:4px; text-decoration:none; font-size:0.9rem; font-weight: bold;">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
