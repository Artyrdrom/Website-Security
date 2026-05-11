<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['percobaan_gagal'])) {
    $_SESSION['percobaan_gagal'] = 0;
}
if (!isset($_SESSION['waktu_kunci'])) {
    $_SESSION['waktu_kunci'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Anti Buffer Overflow
    $username = substr($_POST['username'], 0, 50);
    $password = substr($_POST['password'], 0, 50);

    // --- LOGIKA REGISTRASI (Manual Hash & Salt) ---
    if (isset($_POST['register'])) {
        // 1. Buat Salt acak (32 karakter)
        $salt = bin2hex(random_bytes(16));
        
        // 2. Gabungkan Password asli dengan Salt
        $gabungan_password = $password . $salt;
        
        // 3. Hash gabungan tersebut menggunakan SHA-256
        $hashed_password = hash('sha256', $gabungan_password);
        
        // Insert ke database (masukkan juga variabel $salt ke kolom salt)
        $stmt = $pdo->prepare("INSERT INTO users (username, salt, password) VALUES (:username, :salt, :password)");
        $stmt->execute(['username' => $username, 'salt' => $salt, 'password' => $hashed_password]);
        echo "<p style='color:green;'>Registrasi berhasil! Silakan login.</p>";
    }

    // --- LOGIKA LOGIN ---
    if (isset($_POST['login'])) {
        // Cek Brute Force Lockout
        if ($_SESSION['percobaan_gagal'] >= 3) {
            $waktu_berlalu = time() - $_SESSION['waktu_kunci'];
            if ($waktu_berlalu < 30) {
                $sisa_waktu = 30 - $waktu_berlalu;
                die("<h3 style='color:red;'>Terlalu banyak percobaan gagal! Coba lagi dalam $sisa_waktu detik.</h3>");
            } else {
                $_SESSION['percobaan_gagal'] = 0; 
            }
        }

        // Ambil data user beserta salt-nya (Anti SQL Injection)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            // Verifikasi Manual Hash & Salt
            // 1. Gabungkan password inputan dengan salt yang ada di database
            $gabungan_login = $password . $user['salt'];
            // 2. Hash gabungan tersebut
            $hash_login = hash('sha256', $gabungan_login);

            // 3. Bandingkan hasilnya dengan hash yang tersimpan
            if ($hash_login === $user['password']) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['percobaan_gagal'] = 0; 
                header("Location: komentar.php");
                exit;
            } else {
                // Password salah
                $_SESSION['percobaan_gagal'] += 1;
                $_SESSION['waktu_kunci'] = time();
                sleep(2);
                echo "<p style='color:red;'>Login gagal! Percobaan ke-" . $_SESSION['percobaan_gagal'] . " dari 3.</p>";
            }
        } else {
            // Username tidak ditemukan
            $_SESSION['percobaan_gagal'] += 1;
            $_SESSION['waktu_kunci'] = time();
            sleep(2);
            echo "<p style='color:red;'>Login gagal! Percobaan ke-" . $_SESSION['percobaan_gagal'] . " dari 3.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Proyek CLO 2</h2>
    <form method="POST">
        Username: <input type="text" name="username" maxlength="50" required><br><br>
        Password: <input type="password" name="password" maxlength="50" required><br><br>
        <button type="submit" name="login">Login</button>
        <button type="submit" name="register">Daftar</button>
    </form>
</body>
</html>
