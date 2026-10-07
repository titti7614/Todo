<?php
// services/ajouter_taches.php - Version V3 PDO (To-Do)
// Script logique pure - Aucun code HTML ici

$projet_id        = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$texte_tache      = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$categories_id    = isset($_POST['categories_id']) ? (int)$_POST['categories_id'] : 0;
$nouveau_nom_categories = isset($_POST['nouveau_nom_categories']) ? trim($_POST['nouveau_nom_categories']) : '';
$nouvelle_couleur = isset($_POST['nouvelle_couleur_categories']) ? trim($_POST['nouvelle_couleur_categories']) : '#e67e22';

if ($projet_id > 0 && !empty($texte_tache)) {

    // ÉTAPE A : Création de catégorie à la volée si demandée
    if (!empty($nouveau_nom_categories)) {
        // En PDO, la requête utilise ? au lieu de concaténer des variables échappées
        $sql_ins_categories = "INSERT INTO todo_categories (projet_id, nom_categorie, couleur) VALUES (?, ?, ?)";
        
        try {
            $stmt_ins = $lien->prepare($sql_ins_categories);
            $stmt_ins->execute([$projet_id, $nouveau_nom_categories, $nouvelle_couleur]);
            
            // 🎯 CAPTURE CRITIQUE DE L'ID EN PDO
            $categories_id = (int)$lien->lastInsertId(); 
        } catch (PDOException $e) {
            // Interception du doublon (code 1062) pour récupérer l'ID existant
            if ($e->errorInfo[1] === 1062) {
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

    // ÉTAPE B : Insertion de la tâche liée à l'ID de catégorie stable et nettoyé
    $valeur_categories = $categories_id > 0 ? $categories_id : 0; 

    // Aligné sur la colonne propre 'categorie_id' en base
    $sql_tache = "INSERT INTO todo_taches (projet_id, categorie_id, texte, statut) VALUES (?, ?, ?, 0)";
    
    try {
        $stmt_tache = $lien->prepare($sql_tache);
        $stmt_tache->execute([$projet_id, $valeur_categories, $texte_tache]);
        
        $_SESSION['succes_projet'] = "Tâche ajoutée avec succès !";
    } catch (PDOException $e) {
        $_SESSION['erreur_projet'] = "Erreur technique lors de l'enregistrement de la tâche : " . $e->getMessage();
    }
} else {
    $_SESSION['erreur_projet'] = "Veuillez remplir le libellé de la tâche.";
}

// Redirection propre vers l'index racine
header("Location: http://localhost:8000/index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
