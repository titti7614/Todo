<?php
// ihm/modification_projets.php — Version V4.0.0 Collaborative (Livraison Officielle)
// Consomme les variables globales $projet_a_modifier et $mes_groupes_autorises transmises par l'index.
?>
<div class="form-zone" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
    <h3>✏️ Renommer ou Partager le projet</h3>

    <form action="index.php" method="POST">
        <!-- 🎯 On utilise l'action exacte attendue par index.php -->
        <input type="hidden" name="action" value="modification_projets">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_a_modifier['id']; ?>">

        <!-- 1. Nom du projet -->
        <div style="margin-bottom: 15px;">
            <label for="nom_projet" style="display: block; font-weight: bold; margin-bottom: 5px;">Nom du projet :</label>
            <input type="text" name="nom_projet" id="nom_projet" required value="<?php echo htmlspecialchars($projet_a_modifier['nom_projet'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- 2. 👥 Sélecteur de Partage a posteriori [Nouveauté V4.0.0] -->
        <div style="margin-bottom: 15px;">
            <label for="groupe_id_selectionne" style="display: block; font-weight: bold; margin-bottom: 5px;">👥 Périmètre de partage :</label>
            <select name="groupe_id_selectionne" id="groupe_id_selectionne" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; height: 38px; box-sizing: border-box; font-family: inherit;">
                
                <!-- Option par défaut : le projet reste ou redevient privé -->
                <option value="" <?php echo empty($projet_a_modifier['groupe_id']) ? 'selected' : ''; ?>>🔒 Garder ce projet privé (Solo)</option>
                
                <?php if (!empty($mes_groupes_autorises)): ?>
                    <?php foreach ($mes_groupes_autorises as $groupe): ?>
                        <option value="<?php echo (int)$groupe['id']; ?>" <?php echo (isset($projet_a_modifier['groupe_id']) && (int)$projet_a_modifier['groupe_id'] === (int)$groupe['id']) ? 'selected' : ''; ?>>
                            👥 Partager avec : <?php echo htmlspecialchars($groupe['nom_groupe'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($groupe['collegues'], ENT_QUOTES, 'UTF-8'); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            </select>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" style="background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">💾 Enregistrer les modifications</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_a_modifier['id']; ?>&action=liste" style="background: #95a5a6; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>
