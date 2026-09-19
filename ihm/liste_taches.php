<?php
// ihm/liste_taches.php
// Reçoit de l'index : la ressource brute mysqli_result $resultat et l'entier $projet_id

$taches_tableau = [];
$total_taches = 0;
$taches_a_faire = 0;
$taches_en_cours = 0;
$taches_maitrisees = 0;

// CALCUL DES COMPTEURS v3.0 (Trois états distincts)
if ($resultat && mysqli_num_rows($resultat) > 0) {
    while ($row = mysqli_fetch_assoc($resultat)) {
        $taches_tableau[] = $row;
        $total_taches++;
        if ($row['statut'] == 0) {
            $taches_a_faire++;
        } elseif ($row['statut'] == 1) {
            $taches_en_cours++;
        } elseif ($row['statut'] == 2) {
            $taches_maitrisees++;
        }
    }
}
?>

<!-- En-tête avec titre et compteurs dynamiques v3.0 -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; flex-wrap: wrap; gap: 10px;">
    <h2 style="margin: 0; color: #1e293b;">Liste des tâches</h2>
    
    <?php if ($total_taches > 0): ?>
        <div style="font-size: 0.85rem; font-weight: bold; background: #f1f5f9; padding: 6px 12px; border-radius: 20px; color: #475569; border: 1px solid #cbd5e1; display: flex; gap: 10px;">
            <span title="À faire / Non lu">🔴 À faire : <span style="color: #e74c3c;"><?php echo $taches_a_faire; ?></span></span>
            <span style="color: #cbd5e1;">|</span>
            <span title="En cours / À mémoriser">🟡 En cours : <span style="color: #f1c40f; text-shadow: 0 0 1px #95a5a6;"><?php echo $taches_en_cours; ?></span></span>
            <span style="color: #cbd5e1;">|</span>
            <span title="Maîtrisé / Terminé">🟢 Maîtrisé : <span style="color: #2ecc71;"><?php echo $taches_maitrisees; ?></span></span>
            <span style="color: #cbd5e1;">|</span>
            <span>Total : <span style="color: #3498db;"><?php echo $total_taches; ?></span></span>
        </div>
    <?php endif; ?>
</div>

<div class="liste-taches">
    <?php if (!empty($taches_tableau)): ?>
        <?php foreach ($taches_tableau as $tache): ?>
            <?php 
            // Configuration visuelle selon l'état d'avancement
            $bg_item = '#fff';
            $text_style = 'vertical-align: middle; word-break: break-word;';
            $btn_label = '🔴 À faire';
            $btn_color = '#e74c3c';
            
            if ($tache['statut'] == 1) {
                $bg_item = '#fffdf0'; 
                $btn_label = '🟡 En cours';
                $btn_color = '#f1c40f';
            } elseif ($tache['statut'] == 2) {
                $bg_item = '#f8fafc'; 
                $text_style .= ' text-decoration: line-through; color: #95a5a6;'; 
                $btn_label = '🟢 Maîtrisé';
                $btn_color = '#2ecc71';
            }
            
            // 🎯 CORRECTIF : Vérification stricte de la note pour l'indicateur visuel
            $a_une_note = (!empty($tache['note']) && trim($tache['note']) !== '');
            ?>
            
            <!-- Bloc regroupant une tâche et son tiroir de note -->
            <div style="margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                
                <!-- Ligne principale de la tâche -->
                <div class="tache-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 10px; background: <?php echo $bg_item; ?>; gap: 20px; transition: background 0.2s;">
                    
                    <!-- Colonne 1 : Badge de phase et texte -->
                    <div style="flex: 1;">
                        <span class="badge-phase" style="display: inline-block; background: <?php echo !empty($tache['couleur_phase']) ? htmlspecialchars($tache['couleur_phase'], ENT_QUOTES, 'UTF-8') : '#e67e22'; ?>; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; margin-right: 10px; margin-bottom: 5px; vertical-align: middle; font-weight: bold;">
                            <?php echo !empty($tache['nom_phase']) ? htmlspecialchars($tache['nom_phase'], ENT_QUOTES, 'UTF-8') : 'Général'; ?>
                        </span>
                        <span style="<?php echo $text_style; ?>">
                            <?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    
                    <!-- Colonne 2 : Boutons d'actions -->
                    <div class="actions-tache" style="display: flex; flex-direction: row; gap: 8px; align-items: center; justify-content: flex-end; flex-shrink: 0;">
                        
                        <!-- Bouton d'état cyclique -->
                        <div style="width: 105px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                               style="display: block; color: <?php echo $btn_color; ?>; border: 1px solid <?php echo $btn_color; ?>; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               <?php echo $btn_label; ?>
                            </a>
                        </div>

                        <!-- 📝 Bouton tiroir Note dynamisé -->
                        <div style="width: 85px; flex-shrink: 0; text-align: center;">
                            <button type="button" 
                                    onclick="toggleNoteTiroir(<?php echo $tache['id']; ?>)"
                                    style="display: block; width: 100%; background: <?php echo $a_une_note ? '#e2e8f0' : 'none'; ?>; color: #334155; border: 1px solid #cbd5e1; padding: 6px 0; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; white-space: nowrap; transition: all 0.15s;">
                                📝 <?php echo $a_une_note ? 'Note •' : 'Note'; ?>
                            </button>
                        </div>

                        <!-- Bouton Modifier -->
                        <div style="width: 90px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_taches&id_tache=<?php echo $tache['id']; ?>" 
                               style="display: block; color: #3498db; border: 1px solid #3498db; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               ✏️ Modifier
                            </a>
                        </div>
                        
                        <!-- Bouton Supprimer -->
                        <div style="width: 30px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_tache&id_tache=<?php echo $tache['id']; ?>" 
                               style="display: inline-block; text-decoration: none; font-size: 1rem; padding: 4px 0;" 
                               title="Supprimer la tâche">
                               ❌
                            </a>
                        </div>

                    </div>
                </div>

                <!-- 📝 Tiroir formulaire de Note caché par défaut -->
                <div id="tiroir_note_<?php echo $tache['id']; ?>" style="display: none; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px;">
                    <form action="index.php?action=sauvegarder_note" method="POST" style="margin: 0;">
                        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
                        <input type="hidden" name="id_tache" value="<?php echo (int)$tache['id']; ?>">
                        
                        <textarea name="note_texte" rows="4" 
                                  placeholder="Collez ici vos notes, résumés de lois ou extraits de code utiles pour cette tâche..." 
                                  style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; font-size: 0.85rem; resize: vertical; box-sizing: border-box; background: white;"><?php echo isset($tache['note']) ? htmlspecialchars($tache['note'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        
                        <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                            <button type="submit" style="background: #475569; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='#475569'">
                                Sauvegarder la note
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">Aucune tâche enregistrée pour ce projet ou correspondant à vos filtres.</p>
    <?php endif; ?>
</div>

<script>
// Fonction JavaScript pour ouvrir / fermer le tiroir de note de manière fluide
function toggleNoteTiroir(tacheId) {
    var tiroir = document.getElementById('tiroir_note_' + tacheId);
    if (tiroir.style.display === 'none') {
        tiroir.style.display = 'block';
    } else {
        tiroir.style.display = 'none';
    }
}
</script>
