<?php
// ihm/liste_taches.php
// Vue passive reçue de l'index. Elle consomme la variable de résultat $resultat.

// Sécurité pour la variable $projet_id si non définie
$projet_id = isset($_GET['projet_id']) ? (int)$_GET['projet_id'] : 0;
?>

<!-- BLOC 3 : AFFICHAGE DES TÂCHES ASSOCIÉES -->
<h2>Liste des tâches</h2>
<div class="liste-taches">
   
    <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
        <?php while ($tache = mysqli_fetch_assoc($resultat)): ?>
            
            <div class="tache-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #e2e8f0;">
                <div>
                    <!-- Badge de la phase avec sa couleur dynamique LibreOffice -->
                    <span class="badge-phase" style="background: <?php echo !empty($tache['couleur_phase']) ? $tache['couleur_phase'] : '#7f8c8d'; ?>; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; margin-right: 10px; font-weight: bold;">
                        <?php 
                        if (!empty($tache['nom_phase'])) {
                            echo htmlspecialchars($tache['nom_phase'], ENT_QUOTES, 'UTF-8');
                        } else {
                            echo 'Général';
                        }
                        ?>
                    </span>
                    
                    <!-- Libellé de la tâche (barré ou stylisé selon le statut v3.0) -->
                    <span class="<?php echo ($tache['statut'] == 2) ? 'done' : ''; ?>" style="<?php echo ($tache['statut'] == 2) ? 'text-decoration: line-through; color: #a0aec0;' : ''; ?>">
                        <?php echo htmlspecialchars($tache['texte'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                
                <!-- Zone des boutons d'action du CRUD -->
                <div class="actions-tache" style="display: flex; gap: 8px; align-items: center;">
                    
                    <!-- Indicateur visuel du cycle à 3 états (🔴 -> 🟡 -> 🟢) -->
                    <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=cocher_tache&id_tache=<?php echo $tache['id']; ?>" 
                       title="Changer le statut de maîtrise" 
                       style="text-decoration: none; font-size: 1.1rem;">
                        <?php 
                        if ($tache['statut'] == 1) echo '🟡';
                        elseif ($tache['statut'] == 2) echo '🟢';
                        else echo '🔴';
                        ?>
                    </a>

                    <!-- Bouton Modifier (uniquement si la tâche n'est pas finalisée/verte) -->
                    <?php if ($tache['statut'] != 2): ?>
                        <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=modification_taches&id_tache=<?php echo $tache['id']; ?>" 
                           class="btn-check" 
                           style="color: #3498db; text-decoration: none; font-weight: bold; font-size: 0.9rem;">
                           ✏️ Modifier
                        </a>
                    <?php endif; ?>
                    
                    <!-- Bouton Supprimer -->
                    <a href="index.php?projet_id=<?php echo $projet_id; ?>&action=suppression_tache&id_tache=<?php echo $tache['id']; ?>" 
                       class="btn-delete-task" 
                       title="Supprimer la tâche"
                       style="text-decoration: none;">
                       ❌
                    </a>
                </div>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: #7f8c8d; font-style: italic; text-align: center; padding: 20px 0;">
            Aucune tâche enregistrée ou ne correspond aux filtres pour ce projet.
        </p>
    <?php endif; ?>
</div>
