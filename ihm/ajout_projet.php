<!-- ihm/ajout_projet.php -->
<div class="bloc-form">
    <h3>Créer un nouveau projet</h3>
    
    <!-- CORRECTION : Le formulaire renvoie vers le routeur avec l'action correspondante -->
    <form action="index.php?action=ajout_projet" method="POST">
        <div class="form-ligne">
            <label for="nom_projet">Nom du projet :</label>
            <input type="text" name="nom_projet" id="nom_projet" placeholder="Ex: Mon Super Site Web" required>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button type="submit" name="ajouter_projet" class="btn-blue">Créer le projet</button>
            <a href="index.php?action=liste" style="display: inline-block; padding: 8px 15px; background: #7f8c8d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>
