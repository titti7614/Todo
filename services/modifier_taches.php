<?php
// services/modifier_taches.php
// Script logique pure - Aucun code HTML

$projet_id        = isset($_POST['projet_id']) ? (int)$_POST['projet_id'] : 0;
$id_tache         = isset($_POST['id_tache']) ? (int)$_POST['id_tache'] : 0;
$texte_tache      = isset($_POST['texte_tache']) ? trim($_POST['texte_tache']) : '';
$phase_id         = isset($_POST['phase_id']) ? (int)$_POST['phase_id'] : 0;
$nouveau_nom_phase = isset($_POST['nouveau_nom_phase']) ? trim($_POST['nouveau_nom_phase']) : '';
$nouvelle_couleur = isset($_POST['nouvelle_couleur_phase']) ? trim($_POST['nouvelle_couleur_phase']) : '#34495e';

if ($id_tache > 0 && $projet_id > 0 && !empty($texte_tache)) {
    
    $texte_sec = mysqli_real_escape_string($lien, $texte_tache);
    
    // 💡 ÉTAPE 1 : Si l'utilisateur a créé une nouvelle phase à la volée via le bouton "+"
    if (!empty($nouveau_nom_phase)) {
        $nom_phase_sec = mysqli_real_escape_string($lien, $nouveau_nom_phase);
        $couleur_sec   = mysqli_real_escape_string($lien, $nouvelle_couleur);
        
        // Sécurité doublon : on vérifie si elle existe déjà dans ce projet
        $sql_check_phase = "SELECT id FROM todo_phases WHERE nom = '$nom_phase_sec' AND projet_id = $projet_id LIMIT 1";
        $res_check = mysqli_query($lien, $sql_check_phase);
        
        if ($res_check && mysqli_num_rows($res_check) > 0) {
            $row_phase = mysqli_fetch_assoc($res_check);
            $phase_id  = (int)$row_phase['id'];
        } else {
            // Insertion propre avec la couleur récupérée de l'IHM
            $sql_insert_phase = "INSERT INTO todo_phases (projet_id, nom, couleur) VALUES ($projet_id, '$nom_phase_sec', '$couleur_sec')";
            
            if (mysqli_query($lien, $sql_insert_phase)) {
                // 🎯 CAPTURE CRITIQUE : On récupère le véritable ID généré par MySQL
                $phase_id = (int)mysqli_insert_id($lien);
            }
        }
    }

    // 🎯 ÉTAPE 2 : Mise à jour SQL de la TÂCHE avec le bon phase_id (qui ne peut plus valoir 0 si créée à la volée)
    $sql_update_tache = "UPDATE todo_taches 
                         SET texte = '$texte_sec', phase_id = $phase_id 
                         WHERE id = $id_tache AND projet_id = $projet_id";
    
    if (mysqli_query($lien, $sql_update_tache)) {
        $_SESSION['succes_projet'] = "La tâche a été modifiée avec succès !";
    } else {
        $_SESSION['erreur_projet'] = "Erreur lors de la modification de la tâche.";
    }
} else {
    $_SESSION['erreur_projet'] = "Données du formulaire invalides ou incomplètes.";
}

header("Location: index.php?projet_id=" . $projet_id . "&action=liste");
exit();
?>
