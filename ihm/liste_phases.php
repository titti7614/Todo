<?php
// ihm/liste_phases.php
// Reçoit de l'index : $list_phases et $projet_id
$phase_id_selectionnee = isset($_GET['phase_id']) ? (int)$_GET['phase_id'] : 0;
?>
<div class="zone-phases" style="margin-top: 20px; background: #fdfefe; padding: 15px; border-radius: 4px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
    
    <!-- GAUCHE : Formulaire de filtrage (Soumission automatique) -->
    <form action="index.php" method="GET" style="display: flex; align-items: center; gap: 10px; margin: 0; flex: 1;">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
        <input type="hidden" name="action" value="liste">

        <label for="select_phase" style="font-weight: bold; color: #2c3e50; white-space: nowrap;">📅 Choisir une phase :</label>
        
        <select name="phase_id" id="select_phase" onchange="this.form.submit();" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 200px; width: 100%; background-color: white;">
            <option value="0">-- Toutes les phases --</option>
            <?php if (!empty($list_phases)): ?>
                <?php foreach ($list_phases as $phase): ?>
                    <option value="<?php echo (int)$phase['id']; ?>" <?php echo ($phase_id_selectionnee === (int)$phase['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </form>

    <!-- DROITE : Zone des Actions (Totalement isolée pour éviter les conflits d'affichage) -->
    <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-end; min-width: 150px; flex-shrink: 0;">
        
        <!-- Formulaire de modification (Affiché uniquement si une phase est active) -->
        <form action="index.php" method="POST" id="form_modifier_phase" style="margin: 0; display: <?php echo ($phase_id_selectionnee > 0) ? 'block' : 'none'; ?>;">
            <input type="hidden" name="action" value="modification_phases">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
            <input type="hidden" name="phase_id" id="id_phase_a_renommer" value="<?php echo $phase_id_selectionnee; ?>">
            
            <button type="submit" style="height: 35px; padding: 0 12px; background-color: #3498db; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 0.85rem; cursor: pointer; white-space: nowrap;">
                ✏️ Renommer
            </button>
        </form>

        <!-- 🎯 Le bouton d'ajout vert (Sorti de toute balise conditionnelle ou masquée) -->
        <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=ajout_phases" 
           title="Ajouter une nouvelle phase seule" 
           style="display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 1.2rem; flex-shrink: 0; z-index: 10;">
           +
        </a>
    </div>
</div>
