<!-- ihm/projet_liste.php -->
<!-- NETTOYAGE : Aucun code PHP de chargement ici, l'index s'en est déjà occupé -->

<div class="selector-box">
    <div class="selector-section">
        <label for="proj_select"><strong>Projet actif :</strong></label>
        <select id="proj_select">
            <option value="0">-- Choisir un projet --</option>
            
            <?php foreach ($liste_tous_projets as $p): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $projet_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p['nom_projet'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
            
        </select>
    </div>
    
    <div class="creation-section-inline">
        <a href="index.php?action=ajout_projet" class="btn-orange" style="text-decoration: none; padding: 8px 12px; border-radius: 4px; font-weight: bold;">+ Nouveau Projet</a>
    </div>
</div>
<!-- Tout en bas de ihm/projet_liste.php (ou dans ton index.php) -->
<script>
    const selectElt = document.getElementById('proj_select');
    if (selectElt) {
        selectElt.addEventListener('change', function() {
            const id = this.value;
            if (id > 0) {
                // SÉCURITÉ : On force l'action à 'liste' pour afficher l'IHM du projet choisi
                window.location.href = 'index.php?projet_id=' + id + '&action=liste';
            } else {
                window.location.href = 'index.php?action=liste';
            }
        });
    }
</script>
