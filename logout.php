<?php
// Sertakan kelas Database (meskipun tidak digunakan langsung, untuk konsistensi)
require_once 'koneksi.php';

// Kelas SessionManager untuk mengelola sesi pengguna
class SessionManager {
    // Metode untuk logout pengguna
    public function logout() {
        try {
            // Mulai sesi
            session_start();
            // Hapus semua data sesi
            session_unset();
            session_destroy();
            // Redirect ke login.php
            header("Location: login.php");
            exit();
        } catch (Exception $e) {
            throw new Exception("Gagal logout: " . $e->getMessage());
        }
    }
}

// Inisialisasi pesan kesalahan
$error = '';

// Proses logout jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    try {
        // Inisialisasi kelas SessionManager
        $session = new SessionManager();
        // Jalankan logout
        $session->logout();
    } catch (Exception $e) {
        // Simpan pesan kesalahan
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Metadata -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Toko Online</title>
    <!-- CSS eksternal -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Kontainer logout -->
    <div class="container logout-container">
        <!-- Judul -->
        <h2>Konfirmasi Logout</h2>
        
        <!-- Tampilkan pesan kesalahan -->
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Form konfirmasi logout -->
        <form method="POST" action="">
            <p>Apakah Anda yakin ingin logout?</p>
            <!-- Tombol konfirmasi -->
            <button type="submit" name="confirm_logout" class="btn">Logout</button>
        </form>
        <!-- Tautan kembali -->
        <div class="link">
            <p><a href="index.php">Kembali ke Beranda</a></p>
        </div>
    </div>
</body>
</html>
