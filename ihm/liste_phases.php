<?php
// ihm/liste_phases.php
// Reçoit de l'index : $list_phases, $projet_id, $phases_selectionnees (tableau) et $recherche_mot_cle (chaîne)
?>
<div class="zone-phases" style="margin-top: 20px; background: #f8fafc; padding: 20px; border-radius: 6px; border: 1px solid #e2e8f0;">
    
    <form action="index.php" method="GET" id="form_filtre_multi" style="margin: 0;">
        <!-- Maintien des variables de routage requises dans l'URL -->
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
        <input type="hidden" name="action" value="liste">

        <!-- 🎯 1. LA BARRE DE RECHERCHE TEXTUELLE -->
        <div style="margin-bottom: 18px; position: relative;">
            <label for="recherche_texte" style="display: block; font-weight: bold; color: #1e293b; font-size: 0.95rem; margin-bottom: 6px;">
                🔍 Rechercher par mot-clé (ex: accises, article 60, CI...) :
            </label>
            <div style="display: flex; gap: 10px;">
                <input type="text" name="recherche_texte" id="recherche_texte" 
                       value="<?php echo htmlspecialchars($recherche_mot_cle, ENT_QUOTES, 'UTF-8'); ?>" 
                       placeholder="Tapez votre recherche ici..." 
                       style="flex: 1; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                
                <?php if (!empty($recherche_mot_cle) || !empty($phases_selectionnees)): ?>
                    <!-- Bouton pour tout effacer d'un coup en cas de filtres actifs -->
                    <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" 
                       style="display: flex; align-items: center; justify-content: center; padding: 0 15px; background: #64748b; color: white; text-decoration: none; border-radius: 6px; font-size: 0.85rem; font-weight: bold; white-space: nowrap;">
                       ❌ Réinitialiser les filtres
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Séparateur discret -->
        <div style="border-top: 1px solid #e2e8f0; margin-bottom: 15px;"></div>

        <p style="margin-top: 0; margin-bottom: 12px; font-weight: bold; color: #1e293b; font-size: 0.95rem;">
            📅 Filtrer l'affichage par phase :
        </p>
        
        <!-- Alignement horizontal des filtres et du bouton d'action -->
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px;">
            
            <!-- GAUCHE : Les cases à cocher avec boutons Modifier/Supprimer intégrés -->
            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" 
                   style="display: inline-block; padding: 6px 12px; background: <?php echo empty($phases_selectionnees) ? '#3498db' : '#cbd5e1'; ?>; color: <?php echo empty($phases_selectionnees) ? 'white' : '#475569'; ?>; text-decoration: none; border-radius: 4px; font-size: 0.85rem; font-weight: bold; transition: all 0.2s;">
                   🌐 Tout afficher
                </a>

                <?php if (!empty($list_phases)): ?>
                    <?php foreach ($list_phases as $phase): ?>
                        <?php $est_coche = in_array($phase['id'], $phases_selectionnees); ?>
                        
                        <div style="display: inline-flex; align-items: center; background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid <?php echo $est_coche ? '#3498db' : '#cbd5e1'; ?>; font-size: 0.85rem; font-weight: 500; transition: all 0.15s; box-shadow: <?php echo $est_coche ? '0 0 0 1px #3498db' : 'none'; ?>; gap: 8px;">
                            
                            <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="phases_filtre[]" value="<?php echo (int)$phase['id']; ?>" 
                                       <?php echo $est_coche ? 'checked' : ''; ?>
                                       onchange="document.getElementById('form_filtre_multi').submit();"
                                       style="cursor: pointer; accent-color: #3498db; margin: 0;">
                                <span style="color: #334155;"><?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </label>

                            <span style="color: #e2e8f0; font-size: 0.8rem;">|</span>

                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_phases&phase_id=<?php echo $phase['id']; ?>" 
                               title="Modifier cette phase" 
                               style="text-decoration: none; font-size: 0.75rem; cursor: pointer; filter: grayscale(100%); transition: transform 0.1s;"
                               onmouseover="this.style.filter='none'; this.style.transform='scale(1.2)'" 
                               onmouseout="this.style.filter='grayscale(100%)'; this.style.transform='scale(1)'">
                               ✏️
                            </a>

                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_phase&phase_id=<?php echo $phase['id']; ?>" 
                               title="Supprimer cette phase" 
                               style="text-decoration: none; font-size: 0.75rem; cursor: pointer; filter: grayscale(100%); transition: transform 0.1s;"
                               onmouseover="this.style.filter='none'; this.style.transform='scale(1.2)'" 
                               onmouseout="this.style.filter='grayscale(100%)'; this.style.transform='scale(1)'">
                               ❌
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- DROITE : Bouton d'administration pour créer une phase seule -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=ajout_phases" 
                   title="Créer une nouvelle phase" 
                   style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 1.2rem;">
                   +
                </a>
            </div>
            
        </div>
    </form>
</div>

<script>
// 🎯 DYNAMISME DE RECHERCHE : Lance le filtrage dès que l'utilisateur arrête de taper (Délai de 400ms pour éviter de surcharger la BDD)
var timerRecherche;
document.getElementById('recherche_texte').addEventListener('input', function() {
    clearTimeout(timerRecherche);
    timerRecherche = setTimeout(function() {
        document.getElementById('form_filtre_multi').submit();
    }, 400); 
});

// Force le focus à la fin du texte recherché après rechargement de la page
var inputRecherche = document.getElementById('recherche_texte');
if (inputRecherche.value !== '') {
    inputRecherche.focus();
    var longueurTexte = inputRecherche.value.length;
    inputRecherche.setSelectionRange(longueurTexte, longueurTexte);
}
</script>
