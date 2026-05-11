<?php
$host = 'localhost';
$db   = 'web_keamanan';
$user = 'admin_web';
$pass = 'rahasia123'; // Kosongkan jika root MariaDB tidak dipasangi password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Mengatur mode error PDO ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
