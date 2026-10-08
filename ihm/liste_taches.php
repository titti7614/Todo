<?php
// ihm/liste_taches.php
// Vue passive reçue de l'index. Elle consomme la variable de résultat $resultat.

$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
    <h2 style="margin: 0;">Liste des tâches</h2>
    
    <!-- 🎯 BARRE DE FILTRES RAPIDES EN JAVASCRIPT (Instantané) -->
    <div style="display: flex; gap: 5px; background: #e2e8f0; padding: 4px; border-radius: 6px;">
        <button type="button" onclick="filtrerVueTodo('tous', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: #fff; color: #1e293b; transition: all 0.2s;">
            Tout afficher
        </button>
        <button type="button" onclick="filtrerVueTodo('encours', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: transparent; color: #475569; transition: all 0.2s;">
            🚫 Masquer terminées
        </button>
        <button type="button" onclick="filtrerVueTodo('48h', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: transparent; color: #ea580c; transition: all 0.2s;">
            ⏳ Échéance < 48h
        </button>
        <button type="button" onclick="filtrerVueTodo('urgences', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: transparent; color: #dc2626; transition: all 0.2s;">
            ⚠️ Retards
        </button>
    </div>
</div>

<!-- 🎯 LEVIER ERGONOMIQUE : LÉGENDE COMPACTE SUR UNE SEULE LIGNE + CONSEIL D'UTILISATION FIXE -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.8rem; color: #475569; display: flex; flex-direction: column; gap: 8px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; width: 100%;">
        <!-- Partie Gauche : Statuts -->
        <div style="display: flex; align-items: center; gap: 12px;">
            <strong>Statuts :</strong>
            <span>(🔴 À faire)</span>
            <span>(🟡 En cours)</span>
            <span>(🟢 Terminé)</span>
        </div>
        
        <!-- Partie Droite : Calendrier -->
        <div style="display: flex; align-items: center; gap: 12px;">
            <strong>Alertes calendrier :</strong>
            <span style="background: #ffedd5; color: #ea580c; border: 1px solid #fdbb2d; padding: 1px 6px; border-radius: 4px; font-weight: bold;">📅 Échéance < 48h</span>
            <span style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 1px 6px; border-radius: 4px; font-weight: bold;">📅⚠️ En retard</span>
        </div>
    </div>
    
    <!-- 💡 LIGNE D'ASTUCE FIXE IMMÉDIATE -->
    <div style="color: #2563eb; font-weight: 600; font-size: 0.78rem; border-top: 1px dashed #cbd5e1; padding-top: 6px; display: flex; align-items: center; gap: 4px;">
        💡 <em>Conseil d'utilisation : Cliquez directement sur les ronds de couleur (🔴 🟡 🟢) dans la liste pour faire défiler leur état d'avancement.</em>
    </div>
</div>

<!-- Injection des animations CSS de clignotement -->
<style>
    @keyframes pulseBlink {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; background-color: #fca5a5; transform: scale(0.98); }
        100% { opacity: 1; transform: scale(1); }
    }
    @keyframes pulseOrange {
        0% { opacity: 1; }
        50% { opacity: 0.7; background-color: #fed7aa; }
        100% { opacity: 1; }
    }
    .animate-blink-retard { animation: pulseBlink 1.5s infinite ease-in-out; }
    .animate-blink-today { animation: pulseOrange 2s infinite ease-in-out; }
</style>
<div class="liste-taches">
    <?php if (!empty($resultat) && count($resultat) > 0): ?>
        <?php foreach ($resultat as $tache): ?>
            <?php
                $aujourdhui = date('Y-m-d');
                $dans_48h = date('Y-m-d', strtotime('+2 days'));
                
                $is_depasse = (!empty($tache['date_echeance']) && $tache['date_echeance'] < $aujourdhui && $tache['statut'] != 2);
                $is_proche = (!empty($tache['date_echeance']) && $tache['date_echeance'] >= $aujourdhui && $tache['date_echeance'] <= $dans_48h && $tache['statut'] != 2);
                
                $type_filtre = "normal";
                if ((int)$tache['statut'] === 2) {
                    $type_filtre = "termine";
                } elseif ($is_depasse) {
                    $type_filtre = "retard";
                } elseif ($is_proche) {
                    $type_filtre = "proche";
                }
            ?>
            <div class="tache-item-box" data-status-type="<?php echo $type_filtre; ?>" style="display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #e2e8f0; background: #fff; margin-bottom: 8px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: opacity 0.2s ease;">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex: 1;">
                        
                        <!-- 🎯 COMPOSANT DE BADGES MULTI-CATÉGORIES CORRIGÉ (Explode |||) -->
                        <div style="display: flex; gap: 5px; flex-wrap: wrap; align-items: center;">
                            <?php if (!empty($tache['nom_categories'])): 
                                $noms_categories = explode('|||', $tache['nom_categories']);
                                $couleurs_categories = explode('|||', $tache['couleur_categories']);
                                
                                foreach ($noms_categories as $index => $nom_cat):
                                    $couleur_cat = !empty($couleurs_categories[$index]) ? $couleurs_categories[$index] : '#7f8c8d';
                                    ?>
                                    <span class="badge-categories" style="background: <?php echo $couleur_cat; ?>; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; white-space: nowrap;">
                                        <?php echo htmlspecialchars($nom_cat, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="badge-categories" style="background: #7f8c8d; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; white-space: nowrap;">
                                    Général
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($tache['date_echeance'])): ?>
                            <?php 
                                $date_formatee = date('d/m/Y', strtotime($tache['date_echeance']));
                                $style_badge = "background: #e2e8f0; color: #475569;";
                                $animation_class = ""; 
                                
                                if ($is_depasse) {
                                    $style_badge = "background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-weight: bold;"; 
                                    $animation_class = "animate-blink-retard";
                                } elseif ($is_proche) {
                                    $style_badge = "background: #ffedd5; color: #ea580c; border: 1px solid #fdbb2d; font-weight: bold;"; 
                                    $animation_class = "animate-blink-today";
                                } elseif ($tache['statut'] == 2) {
                                    $style_badge = "background: #f1f5f9; color: #94a3b8; text-decoration: line-through;"; 
                                }
                            ?>
                            <span class="badge-date <?php echo $animation_class; ?>" style="padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; height: 22px; box-sizing: border-box; <?php echo $style_badge; ?>">
                                📅 <?php echo $date_formatee; ?> <?php if ($is_depasse) echo '⚠️'; ?>
                            </span>
                        <?php endif; ?>

                        <span class="<?php echo ($tache['statut'] == 2) ? 'done' : ''; ?>" style="<?php echo ($tache['statut'] == 2) ? 'text-decoration: line-through; color: #a0aec0;' : ''; ?>; font-size: 0.95rem; margin-left: 5px;">
                            <?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    <!-- Actions de la tâche -->
                    <div class="actions-tache" style="display: flex; gap: 8px; align-items: center; margin-left: 15px;">
                        
                        <!-- 🎯 BOUTON DE STATUT CYCLIQUE -->
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                           class="btn-status-cycle"
                           style="text-decoration: none; font-size: 1.1rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; transition: transform 0.2s, background-color 0.2s; box-sizing: border-box;"
                           onmouseover="this.style.transform='scale(1.25)'; this.style.backgroundColor='#f1f5f9';" 
                           onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='transparent';">
                            <?php 
                            if ($tache['statut'] == 1) echo '🟡';
                            elseif ($tache['statut'] == 2) echo '🟢';
                            else echo '🔴';
                            ?>
                        </a>

                        <?php if ($tache['statut'] != 2): ?>
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_taches&id_tache=<?php echo $tache['id']; ?>" class="btn-check" style="color: #3498db; text-decoration: none; font-weight: bold; font-size: 0.9rem;">
                                ✏️ Modifier
                            </a>
                        <?php endif; ?>
                        
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_tache&id_tache=<?php echo $tache['id']; ?>" class="btn-delete-task" title="Supprimer la tâche" style="text-decoration: none;">❌</a>
                    </div>
                </div>

                <!-- Bloc de gestion des Notes -->
                <div style="margin-top: 10px; width: 100%; background: #f8fafc; padding: 8px; border-radius: 4px; border-left: 3px solid #cbd5e1;">
                    <form action="index.php" method="POST" style="display: flex; gap: 8px; margin: 0; align-items: center; width: 100%;">
                        <input type="hidden" name="action" value="sauvegarder_note">
                        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
                        <input type="hidden" name="id_tache" value="<?php echo (int)$tache['id']; ?>">
                        
                        <input type="text" 
                               name="note_texte" 
                               id="input_note_<?php echo $tache['id']; ?>" 
                               value="<?php echo isset($tache['note']) ? htmlspecialchars($tache['note'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                               placeholder="Ajouter une note..." 
                               onfocus="document.getElementById('btn_save_<?php echo $tache['id']; ?>').style.display = 'inline-block';"
                               oninput="document.getElementById('btn_save_<?php echo $tache['id']; ?>').style.display = 'inline-block';"
                               onblur="setTimeout(function(){ if(document.getElementById('input_note_<?php echo $tache['id']; ?>').value.trim() === '') { document.getElementById('btn_save_<?php echo $tache['id']; ?>').style.display = 'none'; } }, 200);"
                               style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; height: 28px; box-sizing: border-box;">
                        
                        <button type="submit" 
                                id="btn_save_<?php echo $tache['id']; ?>"
                                style="display: <?php echo (!empty($tache['note'])) ? 'inline-block' : 'none'; ?>; background: #34495e; color: white; border: none; padding: 0 10px; border-radius: 4px; height: 28px; font-size: 0.85rem; font-weight: bold; cursor: pointer;">
                            💾 Enregistrer
                        </button>

                        <?php if (!empty($tache['note'])): ?>
                            <button type="button" onclick="document.getElementById('input_note_<?php echo $tache['id']; ?>').value = ''; this.form.submit();" style="background: #e74c3c; color: white; border: none; padding: 0 10px; border-radius: 4px; height: 28px; font-size: 0.85rem; font-weight: bold; cursor: pointer;">🧼 Effacer</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">Aucune tâche enregistrée.</p>
    <?php endif; ?>
</div>

<!-- 🎯 SCRIPT JS DE FILTRAGE INSTANTANÉ DE LA VUE -->
<script>
function filtrerVueTodo(mode, boutonActif) {
    // 1. Alternance visuelle des onglets de filtres
    document.querySelectorAll('.btn-filtre-temp').forEach(function(btn) {
        btn.style.background = 'transparent';
        btn.style.color = '#475569';
    });
    boutonActif.style.background = '#ffffff';
    boutonActif.style.color = '#1e293b';

    // 2. Traitement dynamique du masquage sur les containers de tâches
    document.querySelectorAll('.tache-item-box').forEach(function(item) {
        var type = item.getAttribute('data-status-type');
        
        if (mode === 'tous') {
            item.style.display = 'flex';
        } else if (mode === 'encours') {
            if (type === 'termine') {
                item.style.display = 'none';
            } else {
                item.style.display = 'flex';
            }
        } else if (mode === '48h') {
            if (type === 'proche' || type === 'retard') {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        } else if (mode === 'urgences') {
            if (type === 'retard') {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        }
    });
}
</script>
