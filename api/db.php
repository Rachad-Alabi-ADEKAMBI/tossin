<?php
// db.php

$host = 'localhost';      // serveur MySQL
$db   = 'gbemiro'; // nom de la base de données
$user = 'root'; // nom d'utilisateur MySQL
$pass = '';   // mot de passe MySQL
$charset = 'utf8mb4';     // jeu de caractères

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // gestion des erreurs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // éviter les requêtes préparées émulated
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // arrêt du script si la connexion échoue
    exit('Erreur de connexion : ' . $e->getMessage());
}
