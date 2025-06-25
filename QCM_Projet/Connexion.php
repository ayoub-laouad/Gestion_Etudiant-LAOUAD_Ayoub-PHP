<?php
$servername = "localhost";
$username = "root";
$password = '';
$dbname = "insea_2024_2025";

try {
    $lien = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Définir le mode d'erreur PDO sur Exception
    $lien->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully"; // Pour déboguer
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>