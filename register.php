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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #555355 0%, #FAFAFA 50%, #555355 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 2;
            animation: modalAppear 0.3s ease-out;
        }

        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg,rgb(16, 16, 16) 0%,rgb(18, 17, 16) 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .header h2 {
            font-size: 28px;
            font-weight: 300;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .header::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: #8B6F47;
            border-radius: 2px;
        }

        .form-container {
            padding: 40px 30px 30px;
        }

        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #4a4a4a;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        input[type="file"] {
            position: absolute;
            left: -9999px;
            opacity: 0;
        }

        .file-input-label {
            display: inline-block;
            width: 100%;
            padding: 15px;
            background-color: #fafafa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            color: #666;
        }

        .file-input-label:hover {
            background-color: #f0f0f0;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #8B6F47 0%, #A0845C 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 111, 71, 0.3);
        }

        .link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .link a:hover {
            color: #5a67d8;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .logo {
            width: 100px;
            height: auto;
        }

        small {
            display: block;
            margin-top: 5px;
            color: #888;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 10px;
                max-width: 100%;
            }
            
            .form-container {
                padding: 30px 20px 20px;
            }
            
            .header {
                padding: 25px 20px;
            }
            
            .header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <button class="close-btn" onclick="window.history.back()" title="Close">&times;</button>
            <h2>Pendaftaran</h2>
        </div>
        
        <div class="form-container">
            <!-- Logo placeholder removed -->

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
                    <input type="text" id="nama_user" name="nama_user" value="<?= isset($_POST['nama_user']) ? htmlspecialchars($_POST['nama_user']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email_user">Email:</label>
                    <input type="email" id="email_user" name="email_user" value="<?= isset($_POST['email_user']) ? htmlspecialchars($_POST['email_user']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password_user">Password Baru (biarkan kosong jika tidak ingin mengubah):</label>
                    <input type="password" id="password_user" name="password_user" required>
                    <small>Password minimal 8 karakter.</small>
                </div>
                
                <div class="form-group">
                    <label for="foto_user">Foto Profil:</label>
                    <div class="file-input-container">
                        <input type="file" id="foto_user" name="foto_user" accept="image/*">
                        <label for="foto_user" class="file-input-label">
                            <span id="file-chosen">No file chosen</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn">SIMPAN PERUBAHAN</button>
            </form>
            
            <div class="link">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </div>
    </div>

    <script>
        // Handle file input display
        document.getElementById('foto_user').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('file-chosen').textContent = fileName;
        });
    </script>
</body>
</html>