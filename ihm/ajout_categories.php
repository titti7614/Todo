<?php
// ihm/ajout_categories.php
// Vue passive pure : Reçoit de l'index : $projet_id
?>
<div class="zone-formulaires" style="margin-top: 20px;">
    <div class="bloc-form" style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
        <h3>➕ Créer une nouvelle catégorie</h3>
        
        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="ajout_categories">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="nom_categories" style="display:block; font-weight:bold; margin-bottom:5px;">Nom de la nouvelle catégorie :</label>
                <input type="text" name="nom_categories" id="nom_categories" 
                       placeholder="Ex: Étape 1 : Spécifications..." 
                       required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:8px;">Couleur de la catégorie :</label>
                <input type="hidden" name="couleur_categories" id="couleur_categories" value="#e67e22">
                
                <div style="display: grid; grid-template-columns: repeat(6, 35px); gap: 8px; width: max-content;" id="palette_quadrillage_categories">
                    <div data-color="#e67e22" style="background: #e67e22; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 2px #e67e22; transform: scale(1.1);" title="Orange"></div>
                    <div data-color="#34495e" style="background: #34495e; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Gris Ardoise"></div>
                    <div data-color="#2ecc71" style="background: #2ecc71; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Vert"></div>
                    <div data-color="#3498db" style="background: #3498db; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Bleu"></div>
                    <div data-color="#9b59b6" style="background: #9b59b6; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Violet"></div>
                    <div data-color="#e74c3c" style="background: #e74c3c; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Rouge"></div>
                    <div data-color="#f1c40f" style="background: #f1c40f; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Jaune"></div>
                    <div data-color="#1abc9c" style="background: #1abc9c; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Turquoise"></div>
                    <div data-color="#2c3e50" style="background: #2c3e50; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Sombre"></div>
                    <div data-color="#7f8c8d" style="background: #7f8c8d; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Gris"></div>
                    <div data-color="#d35400" style="background: #d35400; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Brique"></div>
                    <div data-color="#27ae60" style="background: #27ae60; width: 35px; height: 35px; border-radius: 4px; cursor: pointer; border: 2px solid white; box-shadow: 0 0 0 1px #cbd5e1;" title="Vert Foncé"></div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" style="background: #2ecc71; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Créer la catégorie</button>
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display:inline-block; padding: 8px 16px; background:#7f8c8d; color:white; border-radius:4px; text-decoration:none; font-size:0.9rem; font-weight: bold;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('#palette_quadrillage_categories > div').forEach(function(pastille) {
    pastille.addEventListener('click', function() {
        var couleur = this.getAttribute('data-color');
        document.getElementById('couleur_categories').value = couleur;
        
        document.querySelectorAll('#palette_quadrillage_categories > div').forEach(function(p) {
            p.style.transform = "scale(1)";
            p.style.boxShadow = "0 0 0 1px #cbd5e1";
        });
        
        this.style.transform = "scale(1.1)";
        this.style.boxShadow = "0 0 0 2px " + couleur;
    });
});
</script>
