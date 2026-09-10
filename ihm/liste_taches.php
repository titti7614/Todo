<?php
// ihm/liste_taches.php
// Reçoit de l'index : la ressource $resultat et le $projet_id
?>
<h2>Liste des tâches</h2>
<div class="liste-taches">
    <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
        <?php while ($tache = mysqli_fetch_assoc($resultat)): ?>
            <!-- Conteneur principal de la ligne -->
            <div class="tache-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 10px; border-bottom: 1px solid #eee; gap: 20px;">
                
                <!-- Colonne 1 : Zone de texte (Prend tout le reste de l'espace à gauche) -->
                <div style="flex: 1;">
                    <!-- Rendu dynamique utilisant couleur_phase et nom_phase extraits de la BDD -->
                    <span class="badge-phase" style="display: inline-block; background: <?php echo !empty($tache['couleur_phase']) ? htmlspecialchars($tache['couleur_phase'], ENT_QUOTES, 'UTF-8') : '#e67e22'; ?>; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; margin-right: 10px; margin-bottom: 5px; vertical-align: middle; font-weight: bold;">
                        <?php echo !empty($tache['nom_phase']) ? htmlspecialchars($tache['nom_phase'], ENT_QUOTES, 'UTF-8') : 'Général'; ?>
                    </span>
                    <span style="vertical-align: middle; word-break: break-word; <?php echo ($tache['statut'] == 1) ? 'text-decoration: line-through; color: #95a5a6;' : ''; ?>">
                        <?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                
                <!-- 🎯 Zone des Actions : Alignée sur la même ligne (row), centrée verticalement -->
                <div class="actions-tache" style="display: flex; flex-direction: row; gap: 10px; align-items: center; justify-content: flex-end; flex-shrink: 0;">
                    
                    <?php if ($tache['statut'] == 0): ?>
                        <!-- Colonne Bouton Modifier (Largeur fixe de 95px pour l'alignement vertical) -->
                        <div style="width: 95px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_taches&id_tache=<?php echo $tache['id']; ?>" 
                               class="btn-check" 
                               style="display: block; color: #3498db; border: 1px solid #3498db; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               ✏️ Modifier
                            </a>
                        </div>
                        
                        <!-- Colonne Bouton Fait (Largeur fixe de 95px) -->
                        <div style="width: 95px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                               class="btn-check" 
                               style="display: block; color: #2ecc71; border: 1px solid #2ecc71; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               ✓ Fait
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Si la tâche est faite, le bouton Rétablir prend la place des colonnes Modifier + Fait -->
                        <div style="width: 200px; flex-shrink: 0; text-align: center;">
                            <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                               class="btn-undo" 
                               style="display: block; color: #e67e22; border: 1px solid #e67e22; padding: 6px 0; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; white-space: nowrap;">
                               ↩️ Rétablir
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Colonne Bouton Supprimer (Largeur fixe de 35px) -->
                    <div style="width: 35px; flex-shrink: 0; text-align: center;">
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_tache&id_tache=<?php echo $tache['id']; ?>" 
                           onclick="return confirm('Supprimer définitivement ?');" 
                           style="display: inline-block; text-decoration: none; font-size: 1rem; padding: 4px 0;" 
                           title="Supprimer">
                           ❌
                        </a>
                    </div>

                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">Aucune tâche enregistrée pour ce projet.</p>
    <?php endif; ?>
</div>
