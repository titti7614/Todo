<?php
// ihm/liste_taches.php (Partie 1/3) - Version Épurée V4
// Vue passive pure. Elle consomme la variable globale $uniquement_les_miennes et $resultat définies par l'index.

$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
    <h2 style="margin: 0;">Liste des tâches</h2>
    
    <!-- 🎯 BARRE DE FILTRES COLLABORATIFS -->
    <div style="display: flex; gap: 5px; background: #e2e8f0; padding: 4px; border-radius: 6px; align-items: center;">
        <?php if (!empty($uniquement_les_miennes)): ?>
            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=liste" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: #2563eb; color: #fff; text-decoration: none; transition: all 0.2s;">
                👥 Voir toute l'équipe
            </a>
        <?php else: ?>
            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=liste&filtre_moi=1" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: #fff; color: #1e293b; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
                👤 Affectées à moi
            </a>
        <?php endif; ?>

        <span style="color: #cbd5e1; margin: 0 2px;">|</span>

        <button type="button" onclick="filtrerVueTodo('tous', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: #fff; color: #1e293b; transition: all 0.2s;">Tout</button>
        <button type="button" onclick="filtrerVueTodo('encours', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: transparent; color: #475569; transition: all 0.2s;">🚫 Masquer terminées</button>
        <button type="button" onclick="filtrerVueTodo('48h', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: transparent; color: #ea580c; transition: all 0.2s;">⏳ < 48h</button>
        <button type="button" onclick="filtrerVueTodo('urgences', this)" class="btn-filtre-temp" style="border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; background: transparent; color: #dc2626; transition: all 0.2s;">⚠️ Retards</button>
    </div>
</div>

<!-- LÉGENDE COMPACTE -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.8rem; color: #475569; display: flex; flex-direction: column; gap: 8px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; width: 100%;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <strong>Statuts :</strong><span>(🔴 À faire)</span><span>(🟡 En cours)</span><span>(🟢 Terminé)</span>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <strong>Alertes :</strong>
            <span style="background: #ffedd5; color: #ea580c; padding: 1px 6px; border-radius: 4px; font-weight: bold;">📅 < 48h</span>
            <span style="background: #fee2e2; color: #dc2626; padding: 1px 6px; border-radius: 4px; font-weight: bold;">📅⚠️ En retard</span>
        </div>
    </div>
</div>
<!-- ihm/liste_taches.php (Partie 2/3) -->
<div class="liste-taches">
    <?php if (!empty($resultat) && count($resultat) > 0): ?>
        <?php foreach ($resultat as $tache): ?>
            <?php
                $aujourdhui = date('Y-m-d');
                $dans_48h = date('Y-m-d', strtotime('+2 days'));
                $is_depasse = (!empty($tache['date_echeance']) && $tache['date_echeance'] < $aujourdhui && $tache['statut'] != 2);
                $is_proche = (!empty($tache['date_echeance']) && $tache['date_echeance'] >= $aujourdhui && $tache['date_echeance'] <= $dans_48h && $tache['statut'] != 2);
                $type_filtre = ((int)$tache['statut'] === 2) ? "termine" : ($is_depasse ? "retard" : ($is_proche ? "proche" : "normal"));
            ?>
            <div class="tache-item-box" data-status-type="<?php echo $type_filtre; ?>" style="display: flex; flex-direction: column; padding: 12px; border-bottom: 1px solid #e2e8f0; background: #fff; margin-bottom: 8px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex: 1;">
                        
                        <!-- Badges Catégories -->
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <?php if (!empty($tache['nom_categories'])): 
                                $noms_categories = explode('|||', $tache['nom_categories']);
                                $couleurs_categories = explode('|||', $tache['couleur_categories']);
                                foreach ($noms_categories as $idx => $nom_cat): ?>
                                    <span style="background: <?php echo !empty($couleurs_categories[$idx]) ? $couleurs_categories[$idx] : '#7f8c8d'; ?>; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;"><?php echo htmlspecialchars($nom_cat, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="background: #7f8c8d; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">Général</span>
                            <?php endif; ?>
                        </div>

                        <!-- Badge Calendrier -->
                        <?php if (!empty($tache['date_echeance'])): ?>
                            <?php 
                                $date_formatee = date('d/m/Y', strtotime($tache['date_echeance']));
                                $style_badge = "background: #e2e8f0; color: #475569;";
                                if ($is_depasse) { $style_badge = "background: #fee2e2; color: #dc2626; font-weight: bold;"; }
                                elseif ($is_proche) { $style_badge = "background: #ffedd5; color: #ea580c; font-weight: bold;"; }
                            ?>
                            <span style="padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; <?php echo $style_badge; ?>">📅 <?php echo $date_formatee; ?></span>
                        <?php endif; ?>

                        <!-- Badge Collaborateur Assigné -->
                        <?php if (!empty($tache['nom_assigne'])): ?>
                            <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                👤 Assigné à : <?php echo htmlspecialchars($tache['nom_assigne'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>

                        <span style="<?php echo ($tache['statut'] == 2) ? 'text-decoration: line-through; color: #a0aec0;' : ''; ?>; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
<!-- ihm/liste_taches.php (Partie 3/3) -->
                    <!-- Actions Cycliques, Modification et Suppression -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" style="text-decoration: none; font-size: 1.1rem;">
                            <?php echo ($tache['statut'] == 1) ? '🟡' : (($tache['statut'] == 2) ? '🟢' : '🔴'); ?>
                        </a>
                        <?php if ($tache['statut'] != 2): ?>
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_taches&id_tache=<?php echo $tache['id']; ?>" style="color: #3498db; text-decoration: none; font-weight: bold; font-size: 0.9rem;">✏️ Modifier</a>
                        <?php endif; ?>
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_tache&id_tache=<?php echo $tache['id']; ?>" style="text-decoration: none;">❌</a>
                    </div>
                </div>

                <!-- 📝 BLOC DES NOTES RESTAURÉ -->
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

<script>
function filtrerVueTodo(mode, boutonActif) {
    document.querySelectorAll('.btn-filtre-temp').forEach(b => { b.style.background = 'transparent'; b.style.color = '#475569'; });
    boutonActif.style.background = '#ffffff'; boutonActif.style.color = '#1e293b';
    document.querySelectorAll('.tache-item-box').forEach(item => {
        var type = item.getAttribute('data-status-type');
        if (mode === 'tous') item.style.display = 'flex';
        else if (mode === 'encours') item.style.display = (type === 'termine') ? 'none' : 'flex';
        else if (mode === '48h') item.style.display = (type === 'proche' || type === 'retard') ? 'flex' : 'none';
        else if (mode === 'urgences') item.style.display = (type === 'retard') ? 'flex' : 'none';
    });
}
</script>
