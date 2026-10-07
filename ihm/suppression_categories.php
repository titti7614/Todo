<?php
// ihm/suppression_categories.php
?>
<div class="zone-formulaires" style="margin-top: 20px;">
    <div class="bloc-form" style="background: #fef2f2; padding: 25px; border-radius: 6px; border: 1px solid #fee2e2;">
        <h3 style="color: #991b1b; margin-top: 0; display: flex; align-items: center; gap: 8px; font-size: 1.2rem;">⚠️ Supprimer définitivement la catégorie ?</h3>
        <p style="color: #374151;">Vous êtes sur le point de supprimer la catégorie : <strong style="color: #991b1b;"><?php echo htmlspecialchars($categories_a_modifier['nom_categorie'], ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
        <p style="color: #b91c1c; font-weight: bold; font-size: 0.95rem;">Attention : Les tâches liées repasseront en "Général". Cette action est irréversible.</p>
        
        <form action="index.php" method="POST" style="display: flex; gap: 12px; margin: 0;">
            <input type="hidden" name="action" value="suppression_categories_confirmee">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
            <input type="hidden" name="categories_id" value="<?php echo (int)$categories_a_modifier['id']; ?>">
            <button type="submit" style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Oui, supprimer</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="background: #6b7280; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;">Annuler</a>
        </form>
    </div>
</div>
