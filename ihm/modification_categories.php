<?php
// ihm/modification_categories.php
if (!isset($categories_a_modifier) || !$categories_a_modifier):
?>
    <div class="alert-danger" style="background:#f8d7da; color:#721c24; padding:15px; border-radius:4px; font-weight:bold;">⚠️ Erreur : La catégorie introuvable.</div>
    <p><a href="index.php?projet_id=<?php echo $projet_id; ?>&action=liste" class="btn-blue">Retour</a></p>
<?php 
else: 
    $couleur_actuelle = !empty($categories_a_modifier['couleur']) ? $categories_a_modifier['couleur'] : '#e67e22';
?>
<div class="zone-formulaires" style="margin-top: 20px;">
    <div class="bloc-form" style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
        <h3>✏️ Modifier la catégorie</h3>
        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="modification_categories">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
            <input type="hidden" name="categories_id" value="<?php echo (int)$categories_a_modifier['id']; ?>">

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="nom_categories" style="display:block; font-weight:bold; margin-bottom:5px;">Nom de la catégorie :</label>
                <input type="text" name="nom_categories" id="nom_categories" value="<?php echo htmlspecialchars($categories_a_modifier['nom_categorie'], ENT_QUOTES, 'UTF-8'); ?>" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:8px;">Couleur de la catégorie :</label>
                <input type="hidden" name="couleur_categories" id="couleur_categories_modif_ecran" value="<?php echo htmlspecialchars($couleur_actuelle, ENT_QUOTES, 'UTF-8'); ?>">
                <div style="display: grid; grid-template-columns: repeat(6, 35px); gap: 8px; width: max-content;" id="palette_quadrillage_modif_ecran">
                    <?php
                    $couleurs = ['#e67e22', '#34495e', '#2ecc71', '#3498db', '#9b59b6', '#e74c3c', '#f1c40f', '#1abc9c', '#2c3e50', '#7f8c8d', '#d35400', '#27ae60'];
                    foreach ($couleurs as $c):
                        $est_active = ($c === $couleur_actuelle);
                    ?>
                        <div data-color="<?php echo $c; ?>" style="background: <?php echo $c; ?>; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: <?php echo $est_active ? '0 0 0 2px ' . $c : '0 0 0 1px #cbd5e1'; ?>; transform: <?php echo $est_active ? 'scale(1.1)' : 'scale(1)'; ?>;" title="<?php echo $c; ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" style="background: #3498db; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Enregistrer</button>
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display:inline-block; padding: 8px 16px; background:#7f8c8d; color:white; border-radius:4px; font-size:0.9rem; font-weight: bold;">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script>
document.querySelectorAll('#palette_quadrillage_modif_ecran > div').forEach(function(pastille) {
    pastille.addEventListener('click', function() {
        var couleur = this.getAttribute('data-color');
        document.getElementById('couleur_categories_modif_ecran').value = couleur;
        document.querySelectorAll('#palette_quadrillage_modif_ecran > div').forEach(function(p) {
            p.style.transform = "scale(1)"; p.style.boxShadow = "0 0 0 1px #cbd5e1";
        });
        this.style.transform = "scale(1.1)"; this.style.boxShadow = "0 0 0 2px " + couleur;
    });
});
</script>
<?php endif; ?>
