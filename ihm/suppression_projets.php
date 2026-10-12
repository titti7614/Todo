<?php
// ihm/suppression_projets.php - Écran de confirmation de destruction V4
?>
<div class="form-zone" style="margin-top: 20px; background: #fff; padding: 25px; border-radius: 4px; border: 1px solid #f5c6cb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h3 style="color: #c0392b; margin-top: 0;">⚠️ Confirmation de suppression définitive</h3>
    <p>Vous êtes sur le point de détruire le projet actif ainsi que <strong>toutes les tâches, catégories, historiques et partages d'équipe</strong> qui lui sont associés.</p>
    <p style="font-weight: bold; color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; border-left: 4px solid #dc2626;">Cette action est irréversible. Confirmez-vous la suppression ?</p>

    <!-- Le formulaire POST qui va réveiller le service réaligné -->
    <form action="index.php" method="POST" style="margin-top: 20px; display: flex; gap: 10px;">
        <input type="hidden" name="action" value="suppression_projets_valide">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
        
        <button type="submit" style="background: #c0392b; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">🗑️ Oui, supprimer définitivement</button>
        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=liste" style="background: #95a5a6; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold; display: inline-flex; align-items: center;">Annuler</a>
    </form>
</div>
