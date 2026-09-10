<?php
// ihm/suppression_projets.php
// Ce fichier ne contient plus AUCUNE logique PHP de base de données, uniquement de l'affichage.

$nom_projet_a_supprimer = "Projet inconnu";

// Si le routeur nous a fourni le projet, on prend son nom
if (!empty($projet_a_supprimer)) {
    $nom_projet_a_supprimer = $projet_a_supprimer['nom_projet'];
}
?>
<div class="container" style="border: 2px solid #e74c3c; padding: 20px; border-radius: 8px; margin-top: 20px; background-color: #fff5f5;">
    <h3 style="color: #e74c3c; margin-top: 0;">⚠️ Supprimer définitivement le projet ?</h3>
    
    <p>Vous êtes sur le point de supprimer le projet : <strong style="color: #c0392b; font-size: 1.1rem;"><?php echo htmlspecialchars($nom_projet_a_supprimer, ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
    <p style="color: #e74c3c; font-weight: bold;">Attention : Cette action est irréversible. Elle supprimera automatiquement toutes les phases et toutes les tâches qui lui sont liées !</p>
    
    <!-- CORRECTION : Le formulaire pointe vers l'index de base, l'action est passée en méthode POST sécurisée -->
    <form action="index.php" method="POST" style="margin-top: 20px;">
        <!-- On passe l'action et l'ID du projet en champs cachés -->
        <input type="hidden" name="action" value="suppression_projets">
        <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" name="confirmer_suppression" class="btn-danger" style="background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Oui, supprimer tout</button>
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display: inline-block; padding: 10px 20px; background: #7f8c8d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Annuler</a>
        </div>
    </form>
</div>
