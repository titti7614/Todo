<?php
// ihm/ajout_taches.php
// Vue passive pure. Elle consomme directement les variables $projet_id et $list_phases transmises par l'index.
?>
<div class="form-zone" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
    <h3>➕ Ajouter une nouvelle tâche</h3>

    <form action="index.php" method="POST">
        <!-- 🎯 CORRECTIF 1 : L'action est synchronisée au pluriel avec l'index -->
        <input type="hidden" name="action" value="ajout_taches">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">

        <!-- 1. Saisie du texte de la tâche -->
        <div style="margin-bottom: 15px;">
            <label for="texte_tache" style="display: block; font-weight: bold; margin-bottom: 5px;">Texte de la tâche :</label>
            <input type="text" name="texte_tache" id="texte_tache" required placeholder="Ex: Rédiger le cahier des charges..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
        </div>

        <!-- 2. Choix de la phase existante numérique -->
        <div id="zone_select_phase" style="margin-bottom: 15px;">
            <label for="phase_id" style="display: block; font-weight: bold; margin-bottom: 5px;">Associer à une phase :</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <select name="phase_id" id="phase_id" style="flex: 1; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; height: 38px;">
                    <option value="0">-- Sélectionner une phase existante (Général) --</option>
                    <?php if (!empty($list_phases)): ?>
                        <?php foreach ($list_phases as $phase): ?>
                            <option value="<?php echo (int)$phase['id']; ?>">
                                <?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button type="button" id="btn_declencher_phase_ajout" title="Créer une nouvelle phase" style="width: 35px; height: 35px; background: #2ecc71; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 1.2rem; cursor: pointer;">+</button>
            </div>
        </div>

        <!-- 3. Champ masqué pour créer la phase à la volée s'il est activé -->
        <div id="bloc_nouvelle_phase_ajout" style="display: none; margin-bottom: 15px; background: #f8fafc; padding: 15px; border-left: 4px solid #2ecc71; border-radius: 4px;">
            <label for="nouveau_nom_phase" style="display: block; font-weight: bold; margin-bottom: 5px; color: #27ae60;">Nom de la nouvelle phase :</label>
            <input type="text" name="nouveau_nom_phase" id="nouveau_nom_phase" placeholder="Ex: Spécifications, Design..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
            <small style="color: #7f8c8d; display: block; margin-top: 5px;">La phase sera créée et la tâche lui sera rattachée automatiquement lors de l'enregistrement.</small>
        </div>

        <!-- Boutons de validation -->
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" style="background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Enregistrer la tâche</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="background: #95a5a6; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>

<script>
document.getElementById('btn_declencher_phase_ajout').addEventListener('click', function(e) {
    e.preventDefault();
    var bloc = document.getElementById('bloc_nouvelle_phase_ajout');
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
