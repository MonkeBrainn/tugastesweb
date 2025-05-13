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
    <style>
        /* CSS untuk halaman login dengan carousel */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            height: 100vh;
        }

        .page-container {
            display: flex;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Login panel styling */
        .login-panel {
            width: 40%;
            min-width: 350px;
            padding: 40px;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        .login-header {
            position: relative;
            margin-bottom: 30px;
            text-align: center;
        }

        .login-header::after {
            content: "";
            display: block;
            width: 50px;
            height: 3px;
            background-color: #8a2be2;
            margin: 10px auto 0;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #8a2be2;
            outline: none;
        }

        .btn {
            width: 100%;
            background-color: #8a2be2;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #7b1fa2;
        }

        .link {
            text-align: center;
            margin-top: 20px;
        }

        .link a {
            color: #8a2be2;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }

        /* Carousel styling */
        .carousel-container {
            width: 60%;
            position: relative;
            background: linear-gradient(135deg, #333, #111);
            overflow: hidden;
        }

        .carousel {
            display: flex;
            height: 100%;
            transition: transform 0.5s ease;
        }

        .carousel-slide {
            min-width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: white;
            background-size: cover;
            background-position: center;
        }

        .slide-content {
            text-align: center;
            max-width: 80%;
        }

        .slide-content h2 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 20px;
        }

        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        /* Navigation arrows */
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
            z-index: 3;
        }

        .carousel-arrow:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        .carousel-arrow-left {
            left: 20px;
        }

        .carousel-arrow-right {
            right: 20px;
        }

        .arrow-icon {
            border: solid white;
            border-width: 0 3px 3px 0;
            display: inline-block;
            padding: 6px;
        }

        .arrow-right {
            transform: rotate(-45deg);
        }

        .arrow-left {
            transform: rotate(135deg);
        }

        /* Carousel indicators */
        .carousel-indicators {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .indicator.active {
            background-color: white;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .page-container {
                flex-direction: column;
            }
            
            .login-panel,
            .carousel-container {
                width: 100%;
            }
            
            .login-panel {
                height: auto;
                padding: 30px 20px;
            }
            
            .carousel-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Login Panel -->
        <div class="login-panel">
            <div class="login-container">
                <!-- Judul -->
                <div class="login-header">
                    <h2>Login</h2>
                </div>

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
                    <button type="submit" class="btn">LOGIN</button>
                </form>

                <!-- Tautan registrasi -->
                <div class="link">
                    <p>Belum punya akun? <a href="register.php"><b>Daftar di sini</b></a></p>
                </div>
            </div>
        </div>

        <!-- Image Carousel -->
        <div class="carousel-container">
            <div class="carousel">
                <div class="carousel-slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/slide1.png');">
                    <div class="slide-content">
                        <h2>ReLuxe</h2>
                        <p></p>
                    </div>
                </div>
                <div class="carousel-slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/slide2.png');">
                    <div class="slide-content">
                        <h2>No Selling Fees</h2>
                        <p>Earn even more on your first 3 listings.</p>
                    </div>
                </div>
                <div class="carousel-slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/slide3.png');">
                    <div class="slide-content">
                        <h2>Pengiriman Cepat</h2>
                        <p>Nikmati layanan pengiriman ekspres ke seluruh Indonesia</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <div class="carousel-arrow carousel-arrow-left">
                <i class="arrow-icon arrow-left"></i>
            </div>
            <div class="carousel-arrow carousel-arrow-right">
                <i class="arrow-icon arrow-right"></i>
            </div>

            <!-- Carousel Indicators -->
            <div class="carousel-indicators">
                <div class="indicator active"></div>
                <div class="indicator"></div>
                <div class="indicator"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Carousel functionality
            const carousel = document.querySelector('.carousel');
            const slides = document.querySelectorAll('.carousel-slide');
            const leftArrow = document.querySelector('.carousel-arrow-left');
            const rightArrow = document.querySelector('.carousel-arrow-right');
            const indicators = document.querySelectorAll('.indicator');
            
            let currentIndex = 0;
            const slideCount = slides.length;
            
            // Function to update carousel position
            function updateCarousel() {
                carousel.style.transform = `translateX(${-currentIndex * 100}%)`;
                
                // Update indicators
                indicators.forEach((indicator, index) => {
                    if (index === currentIndex) {
                        indicator.classList.add('active');
                    } else {
                        indicator.classList.remove('active');
                    }
                });
            }
            
            // Next slide
            function nextSlide() {
                currentIndex = (currentIndex + 1) % slideCount;
                updateCarousel();
            }
            
            // Previous slide
            function prevSlide() {
                currentIndex = (currentIndex - 1 + slideCount) % slideCount;
                updateCarousel();
            }
            
            // Event listeners for arrows
            leftArrow.addEventListener('click', prevSlide);
            rightArrow.addEventListener('click', nextSlide);
            
            // Event listeners for indicators
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    currentIndex = index;
                    updateCarousel();
                });
            });
            
            // Auto-play carousel
            let intervalId = setInterval(nextSlide, 5000);
            
            // Pause auto-play on hover
            carousel.addEventListener('mouseenter', () => {
                clearInterval(intervalId);
            });
            
            // Resume auto-play when mouse leaves
            carousel.addEventListener('mouseleave', () => {
                intervalId = setInterval(nextSlide, 5000);
            });
        });
    </script>
</body>
</html>