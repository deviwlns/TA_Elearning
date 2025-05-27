<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
echo "<h2>Selamat datang, {$user['username']}!</h2>";
echo "<p>Anda login sebagai: <strong>{$user['role']}</strong></p>";
echo "<a href='logout.php' class='btn btn-danger'>Logout</a>";