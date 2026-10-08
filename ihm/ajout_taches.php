<?php
// ihm/ajout_taches.php
// Vue passive pure. Elle consomme directement les variables $projet_id et $list_categories transmises par l'index.
?>
<div class="form-zone" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
    <h3>➕ Ajouter une nouvelle tâche (Multi-Catégories)</h3>

    <form action="index.php" method="POST">
        <input type="hidden" name="action" value="ajout_taches">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">

        <!-- 1. Libellé de la tâche -->
        <div style="margin-bottom: 15px;">
            <label for="texte_tache" style="display: block; font-weight: bold; margin-bottom: 5px;">Texte de la tâche :</label>
            <input type="text" name="texte_tache" id="texte_tache" required placeholder="Ex: Créer une fonctionnalité permettant de mettre un dèlai..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- 2. Sélection Multi-Catégories (Cases à cocher horizontales) -->
        <div id="zone_select_categories" style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px;">Associer à une ou plusieurs catégories :</label>
            
            <?php if (!empty($list_categories)): ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; background: #f8fafc; padding: 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                    <?php foreach ($list_categories as $categories): ?>
                        <label style="display: inline-flex; align-items: center; gap: 6px; background: #fff; padding: 6px 12px; border-radius: 4px; border: 1px solid #cbd5e1; cursor: pointer; font-size: 0.85rem; font-weight: 500; user-select: none; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <!-- 🎯 L'attribut name se termine par [] pour former un tableau en PHP -->
                            <input type="checkbox" name="categories_ids[]" value="<?php echo (int)$categories['id']; ?>" style="margin: 0; cursor: pointer; width: 16px; height: 16px;">
                            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?php echo $categories['couleur']; ?>;"></span>
                            <strong><?php echo htmlspecialchars($categories['nom_categorie'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="font-style: italic; color: #7f8c8d; font-size: 0.85rem; margin-bottom: 12px; background: #f8fafc; padding: 10px; border-radius: 4px; border: 1px dashed #cbd5e1;">Aucune catégorie personnalisée créée pour ce projet. (Général par défaut)</p>
            <?php endif; ?>

            <!-- Bouton pour créer une catégorie à la volée -->
            <button type="button" id="btn_declencher_categories_ajout" style="background: #2ecc71; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                ➕ Créer une nouvelle catégorie
            </button>
        </div>
        <!-- Bloc dynamique d'ajout de nouvelle catégorie (Masqué par défaut) -->
        <div id="bloc_nouvelle_categories_ajout" style="display: none; margin-bottom: 15px; background: #f8fafc; padding: 15px; border-left: 4px solid #2ecc71; border-radius: 4px;">
            <div style="margin-bottom: 12px;">
                <label for="nouveau_nom_categories" style="display: block; font-weight: bold; margin-bottom: 5px; color: #27ae60;">Nom de la nouvelle catégorie :</label>
                <input type="text" name="nouveau_nom_categories" id="nouveau_nom_categories" placeholder="Ex: Spécifications, Design..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <div>
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #27ae60;">Couleur de la catégorie :</label>
                <input type="hidden" name="nouvelle_couleur_categories" id="nouvelle_couleur_categories_ajout" value="#e67e22">
                
                <div style="display: grid; grid-template-columns: repeat(6, 35px); gap: 8px; width: max-content;" id="palette_quadrillage_ajout">
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
            <small style="color: #7f8c8d; display: block; margin-top: 8px;">La nouvelle catégorie sera créée et la tâche y sera associée automatiquement.</small>
        </div>

        <!-- 3. Date d'échéance -->
        <div style="margin-bottom: 15px;">
            <label for="date_echeance" style="display: block; font-weight: bold; margin-bottom: 5px;">Date d'échéance (Optionnelle) :</label>
            <input type="date" name="date_echeance" id="date_echeance" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-family: inherit; box-sizing: border-box; height: 38px;">
        </div>

        <!-- 4. Statut Initial -->
        <div style="margin-bottom: 15px;">
            <label for="statut" style="display: block; font-weight: bold; margin-bottom: 5px;">Statut initial de la tâche :</label>
            <select name="statut" id="statut" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; height: 38px; box-sizing: border-box; font-family: inherit;">
                <option value="0" selected>🔴 À faire</option>
                <option value="1">🟡 En cours</option>
                <option value="2">🟢 Terminé (Régularisation)</option>
            </select>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" style="background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Enregistrer la tâche</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="background: #95a5a6; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>

<script>
document.getElementById('btn_declencher_categories_ajout').addEventListener('click', function(e) {
    e.preventDefault();
    var bloc = document.getElementById('bloc_nouvelle_categories_ajout');
    var input = document.getElementById('nouveau_nom_categories');
    
    if (bloc.style.display === 'none' || bloc.style.display === '') {
        bloc.style.display = 'block'; 
        input.focus(); 
        input.required = true;
    } else {
        bloc.style.display = 'none'; 
        input.value = ""; 
        input.required = false;
    }
});

document.querySelectorAll('#palette_quadrillage_ajout > div').forEach(function(pastille) {
    pastille.addEventListener('click', function() {
        var couleur = this.getAttribute('data-color');
        document.getElementById('nouvelle_couleur_categories_ajout').value = couleur;
        
        document.querySelectorAll('#palette_quadrillage_ajout > div').forEach(function(p) {
            p.style.transform = "scale(1)";
            p.style.boxShadow = "0 0 0 1px #cbd5e1";
        });
        
        this.style.transform = "scale(1.1)";
        this.style.boxShadow = "0 0 0 2px " + couleur;
    });
});
</script>
