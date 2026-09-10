<?php
// ihm/modification_taches.php
// Vue passive. Reçoit de l'index : $tache_a_modifier, $list_phases et $projet_id

if (!$tache_a_modifier): 
?>
    <div class="alert-danger" style="background:#f8d7da; color:#721c24; padding:15px; border-radius:4px; font-weight:bold;">
        ⚠️ Erreur : La tâche demandée est introuvable.
    </div>
    <p><a href="index.php?projet_id=<?php echo $projet_id; ?>&action=liste" class="btn-blue">Retour à la liste</a></p>
<?php 
else: 
?>
<div class="zone-formulaires" style="margin-top: 20px;">
    <div class="bloc-form" style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
        <h3>✏️ Modifier la tâche</h3>
        
        <!-- Envoi au routeur central -->
        <form action="index.php" method="POST">
            <!-- Voyage sécurisé des actions en champs cachés -->
            <input type="hidden" name="action" value="modification_tache">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
            <input type="hidden" name="id_tache" value="<?php echo (int)$tache_a_modifier['id']; ?>">

            <!-- Champ : Texte de la tâche -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="texte_tache" style="display:block; font-weight:bold; margin-bottom:5px;">Texte de la tâche :</label>
                <input type="text" name="texte_tache" id="texte_tache" 
                       value="<?php echo htmlspecialchars($tache_a_modifier['texte'], ENT_QUOTES, 'UTF-8'); ?>" 
                       required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>
            
            <!-- Champ : Sélection de la Phase existante -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="phase_id" style="display:block; font-weight:bold; margin-bottom:5px;">Phase associée :</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <select name="phase_id" id="phase_id" style="flex: 1; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background:white; height: 38px;">
                        <option value="0">-- Sans Phase (Général) --</option>
                        <?php if (!empty($list_phases)): ?>
                            <?php foreach ($list_phases as $phase): ?>
                                <option value="<?php echo (int)$phase['id']; ?>" <?php echo ($tache_a_modifier['phase'] == $phase['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <button type="button" id="btn_declencher_phase_modif" style="width: 35px; height: 35px; background: #2ecc71; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 1.2rem; cursor: pointer;">+</button>
                </div>
            </div>

            <!-- Champ masqué pour créer la phase à la volée pendant la modification -->
            <div id="bloc_nouvelle_phase_modif" style="display: none; margin-bottom: 15px; background: #f8fafc; padding: 15px; border-left: 4px solid #2ecc71; border-radius: 4px;">
                <label for="nouveau_nom_phase" style="display: block; font-weight: bold; margin-bottom: 5px; color: #27ae60;">Créer et attribuer une nouvelle phase :</label>
                <input type="text" name="nouveau_nom_phase" id="nouveau_nom_phase" placeholder="Ex: Conception..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>

            <!-- Boutons de validation -->
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" style="background: #3498db; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Enregistrer les modifications</button>
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display:inline-block; padding: 8px 16px; background:#7f8c8d; color:white; border-radius:4px; text-decoration:none; font-size:0.9rem; font-weight: bold;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('btn_declencher_phase_modif').addEventListener('click', function(e) {
    e.preventDefault();
    var bloc = document.getElementById('bloc_nouvelle_phase_modif');
    var input = document.getElementById('nouveau_nom_phase');
    var select = document.getElementById('phase_id');
    
    if (bloc.style.display === 'none' || bloc.style.display === '') {
        bloc.style.display = 'block'; 
        input.focus(); 
        select.value = "0"; 
        input.required = true;
    } else {
        bloc.style.display = 'none'; 
        input.value = ""; 
        input.required = false;
    }
});
</script>
<?php endif; ?>
