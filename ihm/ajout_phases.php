<?php
// ihm/ajout_phases.php
// Vue passive pure : Reçoit de l'index : $projet_id
?>
<div class="zone-formulaires" style="margin-top: 20px;">
    <div class="bloc-form" style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
        <h3>➕ Créer une nouvelle phase</h3>
        
        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="ajout_phases">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">

            <!-- Champ 1 : Nom de la phase -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="nom_phase" style="display:block; font-weight:bold; margin-bottom:5px;">Nom de la nouvelle phase :</label>
                <input type="text" name="nom_phase" id="nom_phase" 
                       placeholder="Ex: Étape 1 : Spécifications..." 
                       required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>

            <!-- 🎯 NOUVEAU Champ 2 : Choix de la couleur à la création -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="couleur_phase" style="display:block; font-weight:bold; margin-bottom:5px;">Couleur de la phase :</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="color" name="couleur_phase" id="couleur_phase" value="#e67e22" 
                           style="width: 50px; height: 40px; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; padding: 0;">
                    <span style="font-size: 0.9rem; color: #64748b;">Choisissez une couleur pour cette phase</span>
                </div>
            </div>

            <!-- Boutons de validation -->
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" style="background: #2ecc71; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Créer la phase</button>
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display:inline-block; padding: 8px 16px; background:#7f8c8d; color:white; border-radius:4px; text-decoration:none; font-size:0.9rem; font-weight: bold;">Annuler</a>
            </div>
        </form>
    </div>
</div>
