<?php
// 2. TRAITEMENT : Si l'utilisateur ajoute une tâche
if (isset($_POST['ajouter_tache']) && !empty(trim($_POST['texte_tache'])) && $projet_id > 0) {
    $texte = mysqli_real_escape_string($lien, trim($_POST['texte_tache']));
    $phase = mysqli_real_escape_string($lien, $_POST['phase_tache']);

    $sql_ajout = "INSERT INTO todo_list (projet_id, texte, phase) VALUES ($projet_id, '$texte', '$phase')";
    mysqli_query($lien, $sql_ajout);
    header("Location: " . $nom_fichier_actuel . "?projet_id=" . $projet_id);
    exit();
}