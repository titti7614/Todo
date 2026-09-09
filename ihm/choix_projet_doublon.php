<?php
// ihm/choix_doublon.php
// Vue passive : ZÉRO traitement PHP, uniquement l'affichage des variables du routeur.
?>
<div class="container" style="border: 2px solid #3498db; padding: 25px; border-radius: 8px; margin-top: 20px; background-color: #f4f9fc; text-align: center;">
    <h3 style="color: #2980b9; margin-top: 0;">ℹ️ Ce projet existe déjà</h3>
    
    <p style="font-size: 1.05rem; margin-bottom: 25px;">
        Un projet nommé <strong style="color: #2c3e50;">"<?php echo htmlspecialchars($doublon_nom, ENT_QUOTES, 'UTF-8'); ?>"</strong> est déjà en cours d'exécution dans l'application.
    </p>
    
    <div style="display: flex; gap: 15px; justify-content: center;">
        <!-- OPTION 1 : Rejoindre le projet existant avec le bon ID extrait de l'URL -->
        <a href="index.php?projet_id=<?php echo (int)$doublon_id; ?>&action=liste" 
           class="btn-blue" 
           style="text-decoration: none; padding: 12px 20px; display: inline-block; font-weight: bold; font-size: 0.95rem; background: #3498db; color: white; border-radius: 4px;">
           📥 Rejoindre le projet existant
        </a>
        
        <!-- OPTION 2 : Retourner créer un autre projet avec un nouveau nom -->
        <a href="index.php?action=ajout_projet" 
           style="display: inline-block; padding: 12px 20px; background: #7f8c8d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.95rem;">
           🔄 Saisir un autre nom
        </a>
    </div>
</div>
