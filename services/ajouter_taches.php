<?php
// services/ajouter_taches.php - Version Multi-Catégories (Table pivot : todo_tache_categories)

$projet_id              = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$texte_tache            = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$nouveau_nom_categories = isset($_POST['nouveau_nom_categories']) ? trim($_POST['nouveau_nom_categories']) : '';
$nouvelle_couleur       = isset($_POST['nouvelle_couleur_categories']) ? trim($_POST['nouvelle_couleur_categories']) : '#e67e22';
$date_echeance          = (!empty($_POST['date_echeance'])) ? trim($_POST['date_echeance']) : null;
$statut                 = isset($_POST['statut']) ? (int)$_POST['statut'] : 0;

// 🎯 Récupération du tableau de catégories cochées (IHM : name="categories_ids[]")
$categories_ids         = isset($_POST['categories_ids']) && is_array($_POST['categories_ids']) ? $_POST['categories_ids'] : [];

$utilisateur_id = $_SESSION['user_id'] ?? 1;
$role           = $_SESSION['mes_apps_cache']['todo'] ?? 'user';

if ($projet_id > 0 && !empty($texte_tache)) {

    // VERIFICATION DE SÉCURITÉ : Le projet appartient-il à l'utilisateur (ou est-il admin) ?
    if ($role === 'admin') {
        $stmt_verif = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? LIMIT 1");
        $stmt_verif->execute([$projet_id]);
    } else {
        $stmt_verif = $lien->prepare("SELECT id FROM todo_projets WHERE id = ? AND utilisateur_id = ? LIMIT 1");
        $stmt_verif->execute([$projet_id, $utilisateur_id]);
    }
    
    if ($stmt_verif->fetch()) {
        
        try {
            // Début d'une transaction pour garantir l'intégrité des données
            $lien->beginTransaction();

            // ÉTAPE A : Création de la catégorie à la volée si demandée
            if (!empty($nouveau_nom_categories)) {
                $sql_ins_categories = "INSERT INTO todo_categories (projet_id, nom_categorie, couleur) VALUES (?, ?, ?)";
                
                try {
                    $stmt_ins = $lien->prepare($sql_ins_categories);
                    $stmt_ins->execute([$projet_id, $nouveau_nom_categories, $nouvelle_couleur]);
                    // On ajoute la nouvelle catégorie créée au tableau des catégories à associer
                    $categories_ids[] = (int)$lien->lastInsertId(); 
                } catch (PDOException $e) {
                    // Gestion du doublon unique (si la catégorie existe déjà)
                    if (isset($e->errorInfo) && $e->errorInfo[1] === 1062) {
                        $sql_get_categories = "SELECT id FROM todo_categories WHERE projet_id = ? AND nom_categorie = ? LIMIT 1";
                        $stmt_get = $lien->prepare($sql_get_categories);
                        $stmt_get->execute([$projet_id, $nouveau_nom_categories]);
                        $row_categories = $stmt_get->fetch();
                        
                        if ($row_categories) {
                            $categories_ids[] = (int)$row_categories['id'];
                        }
                    } else {
                        throw $e; // Redirige vers le catch principal
                    }
                }
            }

            // ÉTAPE B : Insertion de la tâche principale
            $sql_tache = "INSERT INTO todo_taches (projet_id, categorie_id, texte, statut, utilisateur_id, date_echeance) VALUES (?, 0, ?, ?, ?, ?)";
            
            $stmt_tache = $lien->prepare($sql_tache);
            $stmt_tache->execute([$projet_id, $texte_tache, $statut, $utilisateur_id, $date_echeance]);
            
            // Récupération de l'ID de la tâche fraîchement créée
            $tache_id = (int)$lien->lastInsertId();

            // ÉTAPE C : Association des multi-catégories dans la table pivot harmonisée 🛠️
            if (!empty($categories_ids)) {
                // Nettoyage et dédoublonnage des IDs reçus par sécurité
                $categories_ids = array_unique(array_map('intval', $categories_ids));

                // 🎯 Correction ici : "todo_tache_categories" avec un "s"
                $sql_pivot = "INSERT INTO todo_tache_categories (tache_id, categorie_id) VALUES (?, ?)";
                $stmt_pivot = $lien->prepare($sql_pivot);

                foreach ($categories_ids as $cat_id) {
                    if ($cat_id > 0) {
                        $stmt_pivot->execute([$tache_id, $cat_id]);
                    }
                }
            }
            
            // Validation définitive des insertions
            $lien->commit();
            $_SESSION['succes_projet'] = "Tâche multi-catégories ajoutée avec succès !";

        } catch (PDOException $e) {
            // En cas d'erreur, on annule tout
            $lien->rollBack();
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
