<?php
// services/ajouter_taches.php - Version V3 PDO Multi-User Sécurisée (Fix utilisateur_id)

$projet_id              = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$texte_tache            = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$categories_id          = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;
$nouveau_nom_categories = isset($_POST['nouveau_nom_categories']) ? trim($_POST['nouveau_nom_categories']) : '';
$nouvelle_couleur       = isset($_POST['nouvelle_couleur_categories']) ? trim($_POST['nouvelle_couleur_categories']) : '#e67e22';

$utilisateur_id = $_SESSION['user_id'] ?? 1;
$role           = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

if ($projet_id > 0 && !empty($texte_tache)) {

    // VERIFICATION DE SÉCURITÉ NIVEAU 2 : Le projet appartient-il à l'utilisateur (ou est-il admin) ?
    if ($role === 'admin') {
        $stmt_verif = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? LIMIT 1");
        $stmt_verif->execute([$projet_id]);
    } else {
        $stmt_verif = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1");
        $stmt_verif->execute([$projet_id, $utilisateur_id]);
    }
    
    if ($stmt_verif->fetch()) {
        
        // ÉTAPE A : Création de catégorie à la volée si demandée
        if (!empty($nouveau_nom_categories)) {
            $sql_ins_categories = "INSERT INTO todo_categories (projet_id, nom_categorie, couleur) VALUES (?, ?, ?)";
            
            try {
                $stmt_ins = $lien->prepare($sql_ins_categories);
                $stmt_ins->execute([$projet_id, $nouveau_nom_categories, $nouvelle_couleur]);
                $categories_id = (int)$lien->lastInsertId(); 
            } catch (PDOException $e) {
                if (isset($e->errorInfo) && $e->errorInfo[1] === 1062) {
                    $sql_get_categories = "SELECT id FROM todo_categories WHERE projet_id = ? AND nom_categorie = ? LIMIT 1";
                    $stmt_get = $lien->prepare($sql_get_categories);
                    $stmt_get->execute([$projet_id, $nouveau_nom_categories]);
                    $row_categories = $stmt_get->fetch();
                    
                    if ($row_categories) {
                        $categories_id = (int)$row_categories['id'];
                    }
                }
            }
        }

        // ÉTAPE B : Insertion de la tâche avec la colonne obligatoire 'utilisateur_id'
        $valeur_categories = $categories_id > 0 ? $categories_id : 0; 
        
        // 🎯 FIX DEFINITIF : Injection de l'id utilisateur pour éviter l'erreur technique 1364
        $sql_tache = "INSERT INTO todo_taches (projet_id, categorie_id, texte, statut, utilisateur_id) VALUES (?, ?, ?, 0, ?)";
        
        try {
            $stmt_tache = $lien->prepare($sql_tache);
            $stmt_tache->execute([$projet_id, $valeur_categories, $texte_tache, $utilisateur_id]);
            
            $_SESSION['succes_projet'] = "Tâche ajoutée avec succès !";
        } catch (PDOException $e) {
            $_SESSION['erreur_projet'] = "Erreur technique lors de l'enregistrement de la tâche : " . $e->getMessage();
        }
    } else {
        $_SESSION['erreur_projet'] = "Accès refusé : Action non autorisée sur ce projet.";
    }
} else {
    $_SESSION['erreur_projet'] = "Veuillez remplir le libellé de la tâche.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
