<?php
// ihm/suppression_projets.php
$nom_projet_a_supprimer = !empty($projet_a_supprimer) ? $projet_a_supprimer['nom_projet'] : "Projet inconnu";
?>
<div class="container" style="border: 2px solid #e74c3c; padding: 20px; border-radius: 8px; margin-top: 20px; background-color: #fff5f5;">
    <h3 style="color: #e74c3c; margin-top: 0;">⚠️ Supprimer définitivement le projet ?</h3>
    <p>Vous êtes sur le point de supprimer le projet : <strong style="color: #c0392b; font-size: 1.1rem;"><?php echo htmlspecialchars($nom_projet_a_supprimer, ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
    <p style="color: #e74c3c; font-weight: bold;">Attention : Cela détruira toutes ses catégories et tâches associées !</p>
    
    <form action="index.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="suppression_projets">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
        <input type="hidden" name="confirmer_suppression" value="1">
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-danger" style="background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Oui, supprimer tout</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="padding: 10px 20px; background: #7f8c8d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>
