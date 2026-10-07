<?php
// services/modifier_taches.php - Version V3 PDO Multi-User (Double Verrou Sécurité)
// Script logique pure - Aucun code HTML

$projet_id              = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache               = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$texte_tache            = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$categories_id          = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;
$nouveau_nom_categories = isset($_POST['nouveau_nom_categories']) ? trim($_POST['nouveau_nom_categories']) : '';
$nouvelle_couleur       = isset($_POST['nouvelle_couleur_categories']) ? trim($_POST['nouvelle_couleur_categories']) : '#34495e';

$utilisateur_id = $_SESSION['user_id'] ?? 1;
$role           = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

if ($id_tache > 0 && $projet_id > 0 && !empty($texte_tache)) {
    
    // VERIFICATION DE SECURITE NIVEAU 2 : Le projet ciblé appartient-il bien à l'utilisateur connecté ?
    if ($role === 'admin') {
        $stmt_verif_proj = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? LIMIT 1");
        $stmt_verif_proj->execute([$projet_id]);
    } else {
        $stmt_verif_proj = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1");
        $stmt_verif_proj->execute([$projet_id, $utilisateur_id]);
    }
    
    if ($stmt_verif_proj->fetch()) {
        
        // ÉTAPE 1 : Si l'utilisateur a créé une nouvelle catégorie à la volée via le bouton "+"
        if (!empty($nouveau_nom_categories)) {
            $sql_check_categories = "SELECT id FROM todo_categories WHERE nom_categorie = ? AND projet_id = ? LIMIT 1";
            $stmt_check = $lien->prepare($sql_check_categories);
            $stmt_check->execute([$nouveau_nom_categories, $projet_id]);
            $row_categories = $stmt_check->fetch();
            
            if ($row_categories) {
                $categories_id = (int)$row_categories['id'];
            } else {
                $sql_insert_categories = "INSERT INTO todo_categories (projet_id, nom_categorie, couleur) VALUES (?, ?, ?)";
                $stmt_insert = $lien->prepare($sql_insert_categories);
                
                if ($stmt_insert->execute([$projet_id, $nouveau_nom_categories, $nouvelle_couleur])) {
                    $categories_id = (int)$lien->lastInsertId();
                }
            }
        }

        // ÉTAPE 2 : Mise à jour SQL de la TÂCHE avec double gestion des rôles et des colonnes doublons
        if ($role === 'admin') {
            // L'admin peut modifier n'importe quelle tâche du projet
            $sql_update_tache = "UPDATE todo_taches 
                                 SET texte = ?, categorie_id = ? 
                                 WHERE id = ? AND projet_id = ?";
            $params = [$texte_tache, $categories_id, $id_tache, $projet_id];
        } else {
            // L'user standard est contraint par son identifiant sur les DEUX colonnes pour éviter tout conflit
            $sql_update_tache = "UPDATE todo_taches 
                                 SET texte = ?, categorie_id = ? 
                                 WHERE id = ? AND projet_id = ? AND (utilisateur_id = ? OR user_id = ?)";
            $params = [$texte_tache, $categories_id, $id_tache, $projet_id, $utilisateur_id, $utilisateur_id];
        }
                         
        try {
            $stmt_update = $lien->prepare($sql_update_tache);
            $stmt_update->execute($params);
            
            $_SESSION['succes_projet'] = "La tâche a été modifiée avec succès !";
        } catch (PDOException $e) {
            $_SESSION['erreur_projet'] = "Erreur lors de la modification de la tâche : " . $e->getMessage();
        }
    } else {
        $_SESSION['erreur_projet'] = "Accès refusé : Action non autorisée sur ce projet.";
    }
} else {
    $_SESSION['erreur_projet'] = "Données du formulaire invalides ou incomplètes.";
}

// Redirection relative adaptative Prod
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
