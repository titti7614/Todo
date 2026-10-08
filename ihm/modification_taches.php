<?php
// ihm/modification_taches.php
// Vue passive reçue de l'index. 
// Consomme les variables $tache (contenant 'categories_id' sous forme de tableau d'IDs) et $list_categories.

$projet_id = isset($tache['projet_id']) ? (int)$tache['projet_id'] : 0;
// Sécurité : s'assurer que categories_id est bien un tableau plat d'IDs, sinon on l'initialise vide
$current_categories_ids = isset($tache['categories_id']) && is_array($tache['categories_id']) ? $tache['categories_id'] : [];
?>
<div class="form-zone" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
    <h3>✏️ Modifier la tâche (Multi-Catégories)</h3>

    <form action="index.php" method="POST">
    <!-- 🎯 On utilise l'action exacte attendue par index.php -->
    <input type="hidden" name="action" value="modification_taches">

        <input type="hidden" name="tache_id" value="<?php echo (int)$tache['id']; ?>">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">

        <!-- 1. Libellé de la tâche -->
        <div style="margin-bottom: 15px;">
            <label for="texte_tache" style="display: block; font-weight: bold; margin-bottom: 5px;">Texte de la tâche :</label>
            <input type="text" name="texte_tache" id="texte_tache" required value="<?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- 2. Sélection Multi-Catégories (Cases à cocher horizontales avec pré-cochage dynamique) -->
        <div id="zone_select_categories" style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px;">Associer à une ou plusieurs catégories :</label>
            
            <?php if (!empty($list_categories)): ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; background: #f8fafc; padding: 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                    <?php foreach ($list_categories as $categorie): ?>
                        <?php 
                        // 🎯 Vérification dynamique : si l'ID de la catégorie est présent dans le tableau de la tâche, on coche la case
                        $isChecked = in_array((int)$categorie['id'], $current_categories_ids, true) ? 'checked' : ''; 
                        ?>
                        <label style="display: inline-flex; align-items: center; gap: 6px; background: #fff; padding: 6px 12px; border-radius: 4px; border: 1px solid #cbd5e1; cursor: pointer; font-size: 0.85rem; font-weight: 500; user-select: none; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <!-- 🎯 L'attribut name se termine par [] pour former le tableau attendu par le service PHP -->
                            <input type="checkbox" name="categories_ids[]" value="<?php echo (int)$categorie['id']; ?>" <?php echo $isChecked; ?> style="margin: 0; cursor: pointer; width: 16px; height: 16px;">
                            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?php echo $categorie['couleur']; ?>;"></span>
                            <strong><?php echo htmlspecialchars($categorie['nom_categorie'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="font-style: italic; color: #7f8c8d; font-size: 0.85rem; margin-bottom: 12px; background: #f8fafc; padding: 10px; border-radius: 4px; border: 1px dashed #cbd5e1;">Aucune catégorie disponible sur ce projet.</p>
            <?php endif; ?>
        </div>

        <!-- 3. Date d'échéance -->
        <div style="margin-bottom: 15px;">
            <label for="date_echeance" style="display: block; font-weight: bold; margin-bottom: 5px;">Date d'échéance :</label>
            <input type="date" name="date_echeance" id="date_echeance" value="<?php echo !empty($tache['date_echeance']) ? htmlspecialchars($tache['date_echeance'], ENT_QUOTES, 'UTF-8') : ''; ?>" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-family: inherit; box-sizing: border-box; height: 38px;">
        </div>

        <!-- 4. Statut de la tâche -->
        <div style="margin-bottom: 15px;">
            <label for="statut" style="display: block; font-weight: bold; margin-bottom: 5px;">Statut actuel :</label>
            <select name="statut" id="statut" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; height: 38px; box-sizing: border-box; font-family: inherit;">
                <option value="0" <?php echo ((int)$tache['statut'] === 0) ? 'selected' : ''; ?>>🔴 À faire</option>
                <option value="1" <?php echo ((int)$tache['statut'] === 1) ? 'selected' : ''; ?>>🟡 En cours</option>
                <option value="2" <?php echo ((int)$tache['statut'] === 2) ? 'selected' : ''; ?>>🟢 Terminé</option>
            </select>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" style="background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Enregistrer les modifications</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="background: #95a5a6; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>
