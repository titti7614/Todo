<?php
// services/ajouter_taches.php
// Script logique pure - Aucun code HTML ici

$projet_id        = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$texte_tache      = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$phase_id         = isset($_POST['phase_id']) ? (int)$_POST['phase_id'] : 0;
$nouveau_nom_phase = isset($_POST['nouveau_nom_phase']) ? trim($_POST['nouveau_nom_phase']) : '';
$nouvelle_couleur = isset($_POST['nouvelle_couleur_phase']) ? trim($_POST['nouvelle_couleur_phase']) : '#e67e22';

if ($projet_id > 0 && !empty($texte_tache)) {

    // ÉTAPE A : Phase à la volée
    if (!empty($nouveau_nom_phase)) {
        $nom_phase_sec = mysqli_real_escape_string($lien, $nouveau_nom_phase);
        $couleur_sec   = mysqli_real_escape_string($lien, $nouvelle_couleur);
        
        $sql_ins_phase = "INSERT INTO todo_phases (projet_id, nom, couleur) VALUES ($projet_id, '$nom_phase_sec', '$couleur_sec')";
        
        try {
            if (mysqli_query($lien, $sql_ins_phase)) {
                // 🎯 CAPTURE CRITIQUE
                $phase_id = (int)mysqli_insert_id($lien); 
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $sql_get_phase = "SELECT id FROM todo_phases WHERE projet_id = $projet_id AND nom = '$nom_phase_sec' LIMIT 1";
                $res_phase = mysqli_query($lien, $sql_get_phase);
                if ($res_phase && $row_phase = mysqli_fetch_assoc($res_phase)) {
                    $phase_id = (int)$row_phase['id'];
                }
            }
        }
    }

    // ÉTAPE B : Insertion de la tâche liée à l'ID numérique stable
    $texte_sec = mysqli_real_escape_string($lien, $texte_tache);
    $valeur_phase = $phase_id > 0 ? $phase_id : 0; 

    $sql_tache = "INSERT INTO todo_taches (projet_id, phase_id, texte, statut) 
                  VALUES ($projet_id, $valeur_phase, '$texte_sec', 0)";
    
    if (mysqli_query($lien, $sql_tache)) {
        $_SESSION['succes_projet'] = "Tâche ajoutée avec succès !";
    } else {
        $_SESSION['erreur_projet'] = "Erreur technique lors de l'enregistrement de la tâche : " . mysqli_error($lien);
    }
} else {
    $_SESSION['erreur_projet'] = "Veuillez remplir le libellé de la tâche.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
