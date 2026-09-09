<div class="zone-formulaires">
    <!-- Formulaire : Modifier une tâche -->
    <div class="bloc-form">
        <h3>Modifier la tâche</h3>
        <form action="<?php echo htmlspecialchars($nom_fichier_actuel); ?>" method="POST">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
            <input type="hidden" name="id_tache" value="<?php echo (int)$tache_a_modifier['id']; ?>">

            <div class="form-ligne">
                <label for="texte_tache">Texte de la tâche :</label>
                <input type="text" name="texte_tache" id="texte_tache" 
                       value="<?php echo htmlspecialchars($tache_a_modifier['texte'], ENT_QUOTES, 'UTF-8'); ?>" 
                       required>
            </div>
            
            <div class="form-ligne">
                <label for="phase_tache">Phase :</label>
                <select name="phase_tache" id="phase_tache" required>
                    <option value="Général">-- Sans Phase --</option>
                    <?php 
                    if ($list_phases && mysqli_num_rows($list_phases) > 0): 
                        mysqli_data_seek($list_phases, 0); 
                        while ($phase = mysqli_fetch_assoc($list_phases)): 
                    ?>
                            <option value="<?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?>" 
                                <?php echo ($tache_a_modifier['phase'] == $phase['nom']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" name="modifier_tache" class="btn-blue">Enregistrer les modifications</button>
            <a href="<?php echo htmlspecialchars($nom_fichier_actuel); ?>?projet_id=<?php echo (int)$projet_id; ?>" style="display:inline-block; margin-top:10px; color:#7f8c8d; font-size:0.9rem; text-decoration:none; margin-left: 10px;">Annuler</a>
        </form>
    </div>
</div>
