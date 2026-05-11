<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_komentar'])) {
    $username = $_SESSION['username'];
    $komentar = $_POST['komentar'];

    // Anti SQL Injection (Tetap dipertahankan untuk mengamankan insert data)
    $stmt = $pdo->prepare("INSERT INTO comments (username, komentar) VALUES (:username, :komentar)");
    $stmt->execute(['username' => $username, 'komentar' => $komentar]);
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Halaman Komentar</h2>
    <p>Selamat datang, <?php echo $_SESSION['username']; ?>! <a href="logout.php">Logout</a></p>
    
    <form method="POST">
        Komentar: <textarea name="komentar" required></textarea><br>
        <button type="submit" name="submit_komentar">Kirim Komentar</button>
    </form>

    <h3>Daftar Komentar:</h3>
    <ul>
        <?php
        $stmt = $pdo->query("SELECT * FROM comments");
        while ($row = $stmt->fetch()) {
            // Menampilkan komentar secara langsung TANPA filter anti-XSS
            $tampil_username = $row['username'];
            $tampil_komentar = $row['komentar'];
            echo "<li><strong>$tampil_username:</strong> $tampil_komentar</li>";
        }
        ?>
    </ul>
</body>
</html>
