<?php
// Mulai sesi untuk manajemen login
session_start();


// Jika pengguna sudah login, arahkan ke halaman index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}


// Sertakan file koneksi database
require_once 'koneksi.php';


// Kelas UserRegistration untuk mengelola proses registrasi pengguna
class UserRegistration {
    private $db; // Properti untuk menyimpan instance Database


    // Konstruktor untuk menginisialisasi koneksi database
    public function __construct() {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            throw new Exception("Gagal inisialisasi database: " . $e->getMessage());
        }
    }


    // Metode untuk mendaftarkan pengguna baru
    public function register($data, $file) {
        try {
            $conn = $this->db->getConnection();


            // Sanitasi dan validasi input
            $nama_user = htmlspecialchars(trim($data['nama_user']));
            $email_user = htmlspecialchars(trim($data['email_user']));
            $password_user = trim($data['password_user']);
            $foto_user = $file['foto_user'];


            // Validasi panjang password (minimal 8 karakter)
            if (strlen($password_user) < 8) {
                throw new Exception("Password harus minimal 8 karakter.");
            }


            // Validasi email unik
            $sql_check_email = "SELECT id FROM user WHERE email_user = ?";
            $stmt_check_email = $conn->prepare($sql_check_email);
            $stmt_check_email->bind_param("s", $email_user);
            $stmt_check_email->execute();
            $result_check_email = $stmt_check_email->get_result();
            if ($result_check_email->num_rows > 0) {
                $stmt_check_email->close();
                throw new Exception("Email sudah digunakan.");
            }
            $stmt_check_email->close();


            // Generate ID otomatis: U + YYYY + MM + NNN
            $tahun = date('Y'); // Tahun saat ini, misal 2025
            $bulan = date('m'); // Bulan saat ini, misal 04
            $prefix = "U{$tahun}{$bulan}"; // Contoh: U202504


            // Hitung jumlah pengguna di bulan ini untuk nomor urut
            $sql_count = "SELECT COUNT(*) as total FROM user WHERE id LIKE ?";
            $pattern = "{$prefix}%";
            $stmt_count = $conn->prepare($sql_count);
            $stmt_count->bind_param("s", $pattern);
            $stmt_count->execute();
            $result_count = $stmt_count->get_result();
            $row = $result_count->fetch_assoc();
            $nomor_urut = $row['total'] + 1; // Nomor urut berikutnya
            $stmt_count->close();


            // Format ID dengan 3 digit nomor urut
            $id_user = $prefix . sprintf('%03d', $nomor_urut); // Contoh: U202504001


            // Hash password untuk keamanan
            $hashed_password = password_hash($password_user, PASSWORD_DEFAULT);


            // Handle foto profil
            $foto_path = 'Uploads/user.jpg'; // Default foto
            if ($foto_user['error'] === UPLOAD_ERR_OK) {
                $target_dir = "Uploads/";
                $imageFileType = strtolower(pathinfo($foto_user["name"], PATHINFO_EXTENSION));


                // Validasi file gambar
                $check = getimagesize($foto_user["tmp_name"]);
                if ($check === false || !in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new Exception("File yang diunggah bukan gambar atau format tidak didukung.");
                }


                // Validasi ukuran file (maksimal 2MB)
                if ($foto_user['size'] > 2 * 1024 * 1024) {
                    throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
                }


                // Simpan file dengan nama unik
                $new_file_name = $id_user . "." . $imageFileType; // Gunakan ID sebagai nama file
                $new_file_path = $target_dir . $new_file_name;
                if (!move_uploaded_file($foto_user["tmp_name"], $new_file_path)) {
                    throw new Exception("Gagal mengunggah foto profil.");
                }
                $foto_path = $new_file_path;
            }


            // Simpan pengguna baru ke database
            $sql_insert = "INSERT INTO user (id, nama_user, email_user, password_user, foto_user) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssss", $id_user, $nama_user, $email_user, $hashed_password, $foto_path);
            $stmt_insert->execute();
            $stmt_insert->close();


            return [
                'success' => true,
                'message' => 'Registrasi berhasil. Silakan login.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    // Metode untuk menutup koneksi database
    public function closeConnection() {
        $this->db->closeConnection();
    }
}


// Inisialisasi variabel pesan
$message = '';


try {
    // Buat instance UserRegistration
    $user_registration = new UserRegistration();


    // Proses registrasi jika metode POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = $user_registration->register($_POST, $_FILES);
        $message = $result['message'];
        if ($result['success']) {
            // Redirect ke halaman login dengan pesan sukses
            header("Location: login.php?message=" . urlencode($message));
            exit();
        }
    }


    // Tutup koneksi
    $user_registration->closeConnection();
} catch (Exception $e) {
    $message = "Error: " . htmlspecialchars($e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Toko Online</title>
    <style>
        /* CSS internal untuk responsivitas */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            margin: 0;
        }
        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .message {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .link {
            text-align: center;
            margin-top: 1rem;
        }
        @media (max-width: 600px) {
            .register-container {
                padding: 1rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <h2>Registrasi</h2>


        <!-- Tampilkan pesan sukses atau error -->
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>


        <!-- Form registrasi -->
        <form action="register.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_user">Nama:</label>
                <input type="text" id="nama_user" name="nama_user" value="<?= isset($_POST['nama_user']) ? htmlspecialchars($_POST['nama_user']) : ''; ?>" required aria-describedby="nama_help">
                <small id="nama_help">Masukkan nama lengkap Anda.</small>
            </div>
            <div class="form-group">
                <label for="email_user">Email:</label>
                <input type="email" id="email_user" name="email_user" value="<?= isset($_POST['email_user']) ? htmlspecialchars($_POST['email_user']) : ''; ?>" required aria-describedby="email_help">
                <small id="email_help">Masukkan alamat email yang valid.</small>
            </div>
            <div class="form-group">
                <label for="password_user">Password:</label>
                <input type="password" id="password_user" name="password_user" required aria-describedby="password_help">
                <small id="password_help">Password minimal 8 karakter.</small>
            </div>
            <div class="form-group">
                <label for="foto_user">Foto Profil (opsional):</label>
                <input type="file" id="foto_user" name="foto_user" accept="image/*" aria-describedby="foto_help">
                <small id="foto_help">Unggah gambar (JPG, PNG, GIF, maks 2MB).</small>
            </div>
            <button type="submit" class="btn">Daftar</button>
        </form>
        <div class="link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>
