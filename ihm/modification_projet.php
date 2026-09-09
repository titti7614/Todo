<?php
// ihm/modification_projet.php
// Vue passive : ZÉRO traitement PHP ici.
?>
<div class="bloc-form" style="margin-top: 20px;">
    <h3>✏️ Renommer le projet</h3>
    
    <!-- Affichage passif de la variable du routeur -->
    <?php if (!empty($message_erreur_projet)): ?>
        <div class="alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; border: 1px solid #f5c6cb; margin-bottom: 15px; font-weight: bold;">
            ⚠️ <?php echo htmlspecialchars($message_erreur_projet, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    
    <form action="index.php" method="POST">
        <input type="hidden" name="action" value="modification_projet">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
        
        <div class="form-ligne">
            <label for="nouveau_nom_projet">Nouveau nom du projet :</label>
            <input type="text" name="nouveau_nom_projet" id="nouveau_nom_projet" value="<?php echo htmlspecialchars($nom_actuel, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button type="submit" name="enregistrer_modification_projet" class="btn-blue">Enregistrer</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display: inline-block; padding: 8px 15px; background: #7f8c8d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>
