<?php
$host = "localhost";
$dbname = "elea_ning_devi";
$username = "elea_ning_devi"; // sesuaikan dengan konfigurasimu
$password = "elea_ning_devi"; // sesuaikan dengan konfigurasimu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>