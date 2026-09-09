    <!-- Formulaire : Ajouter une tâche -->
    <div class="bloc-form">
        <h3>Ajouter une nouvelle tâche</h3>
        <form action="<?php echo htmlspecialchars($nom_fichier_actuel); ?>" method="POST">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">

            <div class="form-ligne">
                <label for="texte_tache">Texte de la tâche :</label>
                <input type="text" name="texte_tache" id="texte_tache" 
                       placeholder="Ex: Préparer le rapport..." required>
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
                            <option value="<?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($phase['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" name="ajouter_tache" class="btn-blue">+ Ajouter Tâche</button>
        </form>
    </div>
