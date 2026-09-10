<?php
// services/ajouter_taches.php
// Script logique pure - Aucun code HTML ici

// 1. Récupération stricte des inputs POST du formulaire
$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$texte_tache = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$phase_id = isset($_POST['phase_id']) ? (int)$_POST['phase_id'] : 0;
$nouveau_nom_phase = isset($_POST['nouveau_nom_phase']) ? trim($_POST['nouveau_nom_phase']) : '';

// 2. Traitement si les données obligatoires sont présentes
if ($projet_id > 0 && !empty($texte_tache)) {

    // ÉTAPE A : Si l'utilisateur a saisi une nouvelle phase via le bouton "+"
    if (!empty($nouveau_nom_phase)) {
        $nom_phase_sec = mysqli_real_escape_string($lien, $nouveau_nom_phase);
        
        // 🎯 ALIGNEMENT BDD : Insertion explicite de la couleur par défaut orange pour la phase créée à la volée
        $sql_ins_phase = "INSERT INTO todo_phases (projet_id, nom, couleur) VALUES ($projet_id, '$nom_phase_sec', '#e67e22')";
        
        try {
            if (mysqli_query($lien, $sql_ins_phase)) {
                $phase_id = mysqli_insert_id($lien); // On récupère l'ID numérique généré
            }
        } catch (mysqli_sql_exception $e) {
            // Si la phase existe déjà (doublon bloqué), on va chercher son ID existant
            if ($e->getCode() === 1062) {
                $sql_get_phase = "SELECT id FROM todo_phases WHERE projet_id = $projet_id AND nom = '$nom_phase_sec' LIMIT 1";
                $res_phase = mysqli_query($lien, $sql_get_phase);
                if ($res_phase && $row_phase = mysqli_fetch_assoc($res_phase)) {
                    $phase_id = (int)$row_phase['id'];
                }
            }
        }
    }

    // ÉTAPE B : Insertion de la tâche liée à l'ID numérique de la phase
    $texte_sec = mysqli_real_escape_string($lien, $texte_tache);
    $valeur_phase = $phase_id > 0 ? $phase_id : 0; // 0 représente la phase par défaut "Général"

    // 🎯 REQUÊTE PURE : Alignée sur vos exactes colonnes (projet_id, phase, texte, statut)
    $sql_tache = "INSERT INTO todo_taches (projet_id, phase, texte, statut) 
                  VALUES ($projet_id, $valeur_phase, '$texte_sec', 0)";
    
    if (mysqli_query($lien, $sql_tache)) {
        $_SESSION['succes_projet'] = "Tâche ajoutée avec succès !";
    } else {
        $_SESSION['erreur_projet'] = "Erreur technique lors de l'enregistrement de la tâche : " . mysqli_error($lien);
    }
} else {
    $_SESSION['erreur_projet'] = "Veuillez remplir le libellé de la tâche.";
}

// 3. Redirection propre pour vider le flux POST et rafraîchir l'IHM
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
