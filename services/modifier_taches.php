<?php
// services/modifier_taches.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML

$projet_id        = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache         = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$texte_tache      = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$categories_id    = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;
$nouveau_nom_categories = isset($_POST['nouveau_nom_categories']) ? trim($_POST['nouveau_nom_categories']) : '';
$nouvelle_couleur = isset($_POST['nouvelle_couleur_categories']) ? trim($_POST['nouvelle_couleur_categories']) : '#34495e';

if ($id_tache > 0 && $projet_id > 0 && !empty($texte_tache)) {
    
    // 💡 ÉTAPE 1 : Si l'utilisateur a créé une nouvelle catégorie à la volée via le bouton "+"
    if (!empty($nouveau_nom_categories)) {
        
        // Sécurité doublon : on vérifie si elle existe déjà dans ce projet (aligné sur nom_categorie)
        $sql_check_categories = "SELECT id FROM todo_categories WHERE nom_categorie = ? AND projet_id = ? LIMIT 1";
        $stmt_check = $lien->prepare($sql_check_categories);
        $stmt_check->execute([$nouveau_nom_categories, $projet_id]);
        $row_categories = $stmt_check->fetch();
        
        if ($row_categories) {
            $categories_id  = (int)$row_categories['id'];
        } else {
            // Insertion propre avec la couleur récupérée de l'IHM
            $sql_insert_categories = "INSERT INTO todo_categories (projet_id, nom_categorie, couleur) VALUES (?, ?, ?)";
            $stmt_insert = $lien->prepare($sql_insert_categories);
            
            if ($stmt_insert->execute([$projet_id, $nouveau_nom_categories, $nouvelle_couleur])) {
                // 🎯 CAPTURE CRITIQUE : On récupère le véritable ID généré par MySQL en PDO
                $categories_id = (int)$lien->lastInsertId();
            }
        }
    }

    // 🎯 ÉTAPE 2 : Mise à jour SQL de la TÂCHE avec le bon categorie_id en requêtes préparées
    $sql_update_tache = "UPDATE todo_taches 
                         SET texte = ?, categorie_id = ? 
                         WHERE id = ? AND projet_id = ?";
                         
    try {
        $stmt_update = $lien->prepare($sql_update_tache);
        $stmt_update->execute([$texte_tache, $categories_id, $id_tache, $projet_id]);
        
        $_SESSION['succes_projet'] = "La tâche a été modifiée avec succès !";
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur lors de la modification de la tâche : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Données du formulaire invalides ou incomplètes.";
}

// Redirection alignée sur l'URL racine locale
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
