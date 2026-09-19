<?php
// ihm/liste_taches.php
// Reçoit de l'index : la ressource brute mysqli_result $resultat et l'entier $projet_id

$taches_tableau = [];
$total_taches = 0;
$taches_en_cours = 0;

// 🎯 CALCUL DU COMPTEUR : On stocke les résultats dans un tableau et on compte les statuts
if ($resultat && mysqli_num_rows($resultat) > 0) {
    while ($row = mysqli_fetch_assoc($resultat)) {
        $taches_tableau[] = $row;
        $total_taches++;
        if ($row['statut'] == 0) {
            $taches_en_cours++;
        }
    }
}
?>

<!-- En-tête avec titre et compteurs dynamiques -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
    <h2 style="margin: 0; color: #1e293b;">Liste des tâches</h2>
    
    <?php if ($total_taches > 0): ?>
        <div style="font-size: 0.9rem; font-weight: bold; background: #f1f5f9; padding: 6px 12px; border-radius: 20px; color: #475569; border: 1px solid #cbd5e1;">
            ⏳ En cours : <span style="color: #e67e22;"><?php echo $taches_en_cours; ?></span> 
            / Total : <span style="color: #3498db;"><?php echo $total_taches; ?></span>
        </div>
    <?php endif; ?>
</div>

<div class="liste-taches">
    <?php if (!empty($taches_tableau)): ?>
        <?php foreach ($taches_tableau as $tache): ?>
            <!-- Conteneur principal de la ligne de tâche -->
            <div class="tache-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 10px; border-bottom: 1px solid #eee; gap: 20px; background: <?php echo ($tache['statut'] == 1) ? '#f8fafc' : '#fff'; ?>;">
                
                <!-- Colonne 1 : Badge de phase et texte de la tâche -->
                <div style="flex: 1;">
                    <span class="badge-phase" style="display: inline-block; background: <?php echo !empty($tache['couleur_phase']) ? htmlspecialchars($tache['couleur_phase'], ENT_QUOTES, 'UTF-8') : '#e67e22'; ?>; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; margin-right: 10px; margin-bottom: 5px; vertical-align: middle; font-weight: bold;">
                        <?php echo !empty($tache['nom_phase']) ? htmlspecialchars($tache['nom_phase'], ENT_QUOTES, 'UTF-8') : 'Général'; ?>
                    </span>
                    <span style="vertical-align: middle; word-break: break-word; <?php echo ($tache['statut'] == 1) ? 'text-decoration: line-through; color: #95a5a6;' : ''; ?>">
                        <?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                
                <!-- Colonne 2 : Zone des boutons d'actions -->
                <div class="actions-tache" style="display: flex; flex-direction: row; gap: 10px; align-items: center; justify-content: flex-end; flex-shrink: 0;">
                    
                    <?php if ($tache['statut'] == 0): ?>
                        <!-- Bouton Modifier -->
                        <div style="width: 95px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_taches&id_tache=<?php echo $tache['id']; ?>" 
                               class="btn-check" 
                               style="display: block; color: #3498db; border: 1px solid #3498db; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               ✏️ Modifier
                            </a>
                        </div>
                        
                        <!-- Bouton Fait -->
                        <div style="width: 95px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                               class="btn-check" 
                               style="display: block; color: #2ecc71; border: 1px solid #2ecc71; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               ✓ Fait
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Bouton Rétablir -->
                        <div style="width: 200px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                               class="btn-undo" 
                               style="display: block; color: #e67e22; border: 1px solid #e67e22; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               ↩️ Rétablir
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Bouton Supprimer épuré -->
                    <div style="width: 35px; flex-shrink: 0; text-align: center;">
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_tache&id_tache=<?php echo $tache['id']; ?>" 
                           style="display: inline-block; text-decoration: none; font-size: 1rem; padding: 4px 0;" 
                           title="Supprimer la tâche">
                           ❌
                        </a>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">Aucune tâche enregistrée pour ce projet ou correspondant à vos filtres.</p>
    <?php endif; ?>
</div>
