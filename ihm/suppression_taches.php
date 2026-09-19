<?php
// ihm/suppression_taches.php
// Vue passive : s'affiche uniquement si $tache_a_modifier est chargée par l'index
?>
<div class="zone-formulaires" style="margin-top: 20px;">
    <div class="bloc-form" style="background: #fef2f2; padding: 25px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #fee2e2;">
        
        <h3 style="color: #991b1b; margin-top: 0; display: flex; align-items: center; gap: 8px; font-size: 1.2rem;">
            ⚠️ Supprimer définitivement cette tâche ?
        </h3>
        
        <p style="color: #374151; font-size: 1rem; margin-bottom: 15px;">
            Vous êtes sur le point de supprimer la tâche : <strong style="color: #991b1b; font-weight: bold;">"<?php echo htmlspecialchars($tache_a_modifier['texte'], ENT_QUOTES, 'UTF-8'); ?>"</strong>.
        </p>
        
        <p style="color: #b91c1c; font-weight: bold; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5;">
            Attention : Cette action est irréversible. L'historique et le statut de cette tâche seront définitivement effacés de votre plan.
        </p>
        
        <!-- Formulaire de validation finale en POST sécurisé -->
        <form action="index.php" method="POST" style="display: flex; gap: 12px; margin: 0;">
            <input type="hidden" name="action" value="suppression_tache_confirmee">
            <input type="hidden" name="projet_id" value="<?php echo (int)$projet_id; ?>">
            <input type="hidden" name="id_tache" value="<?php echo (int)$tache_a_modifier['id']; ?>">
            
            <button type="submit" name="confirmer_suppression" style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; font-size: 0.95rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                Oui, supprimer la tâche
            </button>
            
            <a href="index.php?projet_id=<?php echo (int)$projet_id; ?>&action=liste" style="display: inline-block; background: #6b7280; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.95rem; text-align: center; transition: background 0.2s;" onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'">
                Annuler
            </a>
        </form>
    </div>
</div>
