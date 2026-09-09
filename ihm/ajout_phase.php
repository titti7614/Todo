 
 <!-- Formulaire pour ajouter une nouvelle phase -->
                <div class="bloc-form">
                    <h3>Ajouter une nouvelle phase</h3>
                    <form action="<?php echo $nom_fichier_actuel; ?>" method="POST">
                        <input type="hidden" name="projet_id" value="<?php echo $projet_id; ?>">
                        <div class="form-ligne">
                            <label for="nom_phase">Nom de la phase :</label>
                            <input type="text" name="nom_phase" id="nom_phase" placeholder="Ex: Planification" required>
                        </div>
                        <button type="submit" name="ajouter_phase" class="btn-green">+ Ajouter Phase</button>
                    </form>
                </div>
<?php 
// Inclure le traitement pour ajouter une phase 
require_once '../services/ajouter_phases.php';         
