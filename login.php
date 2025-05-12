<?php
// Sertakan kelas Database
require_once 'koneksi.php';

// Kelas Auth untuk autentikasi pengguna
class Auth {
    // Properti privat untuk instance Database
    private $db;

    // Konstruktor untuk inisialisasi koneksi
    public function __construct() {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            throw new Exception("Gagal inisialisasi database: " . $e->getMessage());
        }
    }

    // Metode untuk login pengguna
    public function login($nama_user, $password_user) {
        try {
            // Validasi input
            if (empty($nama_user) || empty($password_user)) {
                throw new Exception("Nama pengguna atau password tidak boleh kosong!");
            }

            // Sanitasi nama pengguna
            $nama_user = htmlspecialchars(trim($nama_user));

            // Query untuk mencari pengguna
            $conn = $this->db->getConnection();
            $sql = "SELECT id, nama_user, password_user, foto_user FROM user WHERE nama_user = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Gagal menyiapkan query: " . $conn->error);
            }

            // Bind dan eksekusi
            $stmt->bind_param("s", $nama_user);
            $stmt->execute();
            $result = $stmt->get_result();

            // Periksa hasil query
            if ($result->num_rows === 0) {
                throw new Exception("Nama pengguna tidak ditemukan!");
            }

            // Ambil data pengguna
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (!password_verify($password_user, $user['password_user'])) {
                throw new Exception("Password salah!");
            }

            // Inisialisasi sesi
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama_user'] = $user['nama_user'];
            $_SESSION['foto_user'] = $user['foto_user'] ?: 'Uploads/user.jpg';

            // Tutup statement
            $stmt->close();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Metode untuk menutup koneksi
    public function closeConnection() {
        $this->db->closeConnection();
    }
}

// Inisialisasi pesan kesalahan
$error = '';

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data form
    $nama_user = isset($_POST['nama_user']) ? trim($_POST['nama_user']) : '';
    $password_user = isset($_POST['password_user']) ? $_POST['password_user'] : '';

    try {
        // Inisialisasi kelas Auth
        $auth = new Auth();
        // Coba login
        if ($auth->login($nama_user, $password_user)) {
            // Redirect ke index.php
            header("Location: index.php");
            exit();
        }
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
    <title>Login - Toko Online</title>
    <!-- CSS eksternal -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Kontainer login -->
    <div class="container login-container">
        <!-- Judul -->
        <h2>Login</h2>

        <!-- Tampilkan pesan kesalahan -->
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Form login -->
        <form method="POST" action="">
            <!-- Input nama pengguna -->
            <div class="form-group">
                <label for="nama_user">Nama User:</label>
                <input type="text" id="nama_user" name="nama_user" placeholder="Masukkan nama pengguna" required>
            </div>
            <!-- Input password -->
            <div class="form-group">
                <label for="password_user">Password:</label>
                <input type="password" id="password_user" name="password_user" placeholder="Masukkan password" required>
            </div>
            <!-- Tombol submit -->
            <button type="submit" class="btn">Login</button>
        </form>

        <!-- Tautan registrasi -->
        <div class="link">
            <p>Belum punya akun? <a href="register.php"><b>Daftar di sini</b></a></p>
        </div>
    </div>
</body>
</html>
