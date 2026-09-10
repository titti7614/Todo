<?php
// services/modifier_taches.php
// Script logique pure - Aucun code HTML ici

$projet_id = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$texte_tache = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$phase_id = isset($_POST['phase_id']) ? (int)$_POST['phase_id'] : 0;
$nouveau_nom_phase = isset($_POST['nouveau_nom_phase']) ? trim($_POST['nouveau_nom_phase']) : '';

if ($id_tache > 0 && $projet_id > 0 && !empty($texte_tache)) {
    
    // ÉTAPE A : Si l'utilisateur a créé une phase à la volée pendant la modification
    if (!empty($nouveau_nom_phase)) {
        $nom_phase_sec = mysqli_real_escape_string($lien, $nouveau_nom_phase);
        $sql_ins_phase = "INSERT INTO todo_phases (projet_id, nom) VALUES ($projet_id, '$nom_phase_sec')";
        
        try {
            if (mysqli_query($lien, $sql_ins_phase)) {
                $phase_id = mysqli_insert_id($lien); // On récupère l'identifiant numérique tout neuf
            }
        } catch (mysqli_sql_exception $e) {
            // Si doublon, on récupère l'ID existant
            if ($e->getCode() === 1062) {
                $sql_get_phase = "SELECT id FROM todo_phases WHERE projet_id = $projet_id AND nom = '$nom_phase_sec' LIMIT 1";
                $res_phase = mysqli_query($lien, $sql_get_phase);
                if ($res_phase && $row_phase = mysqli_fetch_assoc($res_phase)) {
                    $phase_id = (int)$row_phase['id'];
                }
            }
        }
    }

    $texte_sec = mysqli_real_escape_string($lien, $texte_tache);
    $valeur_phase = $phase_id > 0 ? $phase_id : 0; // 0 représente la phase "Général"
    
    // 🎯 REQUÊTE CORRIGÉE : Utilisation de votre colonne réelle 'phase' pour stocker la relation numérique
    $sql_update = "UPDATE todo_taches 
                   SET texte = '$texte_sec', phase = $valeur_phase 
                   WHERE id = $id_tache";
                   
    if (mysqli_query($lien, $sql_update)) {
        $_SESSION['succes_projet'] = "Tâche mise à jour avec succès !";
    } else {
        $_SESSION['erreur_projet'] = "Erreur technique lors de la modification.";
    }
}

// Redirection vers le Routeur central
header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
