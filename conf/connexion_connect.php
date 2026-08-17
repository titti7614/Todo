<?php
// Identifiants de connexion MySQL de ton Mac
if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '@Dm!n');
}

/**
 * Initialise et retourne la connexion à la base de données centrale du To-Do.
 * 
 * @return mysqli
 */
function getTodoDatabaseConnection() 
{
    $db_name = "central_todo_db";
    $lien = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, $db_name);
    
    if (!$lien) {
        die("Erreur de connexion à la base To-Do : " . mysqli_connect_error());
    }
    
    mysqli_set_charset($lien, "utf8mb4");
    return $lien;
}
