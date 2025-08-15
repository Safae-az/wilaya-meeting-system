<?php
$host = 'localhost';
$dbname = 'reunion_db';
$user = 'root'; // change selon ta config
$pass = '';     // mot de passe de ton MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
