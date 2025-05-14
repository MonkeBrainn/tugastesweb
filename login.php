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
    <title>ReLuxe - Where fashion finds a second life</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Add Alice font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Alice&display=swap" rel="stylesheet">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            overflow: hidden;
            height: 100vh;
            background-color: #f8f5f2;
            position: relative;
        }

        /* Full-screen image container */
        .fullscreen-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Carousel styling */
        .carousel {
            height: 100%;
            width: 100%;
            position: relative;
        }

        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .carousel-slide.active {
            opacity: 1;
        }

        /* Navigation controls */
        .carousel-controls {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .indicator.active {
            background-color: white;
            transform: scale(1.2);
        }

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

        /* Login panel */
        .login-container {
            position: absolute;
            top: 0;
            right: 0;
            width: 400px;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            z-index: 100;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: -5px 0 20px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }

        .login-form {
            width: 100%;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .login-header::after {
            content: "";
            display: block;
            width: 50px;
            height: 3px;
            background-color: #c5a47e;
            margin: 10px auto 0;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
            border-color: #c5a47e;
            outline: none;
            box-shadow: 0 0 0 2px rgba(197, 164, 126, 0.2);
        }

        .btn {
            width: 100%;
            background-color: #c5a47e;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            background-color: #b2916a;
            transform: translateY(-2px);
        }

        .link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
        }

        .link a {
            color: #c5a47e;
            text-decoration: none;
            font-weight: 600;
        }

        .link a:hover {
            text-decoration: underline;
        }

        /* Logo with Alice font */
        .brand-logo {
            position: absolute;
            top: 30px;
            left: 40px;
            z-index: 5;
        }

        .brand-logo h1 {
            color: white;
            font-size: 2.2rem;
            font-weight: 400; /* Reduced to match Alice font style */
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            font-family: 'Alice', serif; /* Alice font applied here */
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .login-container {
                width: 100%;
                right: 0;
                padding: 30px 20px;
            }

            .brand-logo {
                top: 20px;
                left: 20px;
            }

            .brand-logo h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Full-screen background image and carousel -->
    <div class="fullscreen-container">
        <!-- Brand Logo with Alice font -->
        <div class="brand-logo">
            <h1>ReLuxe</h1>
        </div>

        <!-- Carousel - Removed text overlay and gradient -->
        <div class="carousel">
            <div class="carousel-slide active" style="background-image: url('images/slide1.png');">
                <!-- Removed slide content -->
            </div>
            <div class="carousel-slide" style="background-image: url('images/slide2.png');">
                <!-- Removed slide content -->
            </div>
            <div class="carousel-slide" style="background-image: url('images/slide3.png');">
                <!-- Removed slide content -->
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
        <div class="carousel-controls">
            <div class="indicator active"></div>
            <div class="indicator"></div>
            <div class="indicator"></div>
        </div>
    </div>

    <!-- Login panel -->
    <div class="login-container">
        <div class="login-form">
            <!-- Heading -->
            <div class="login-header">
                <h2>Welcome Back</h2>
            </div>

            <!-- Error message if any -->
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Login form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama_user">Username</label>
                    <input type="text" id="nama_user" name="nama_user" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password_user">Password</label>
                    <input type="password" id="password_user" name="password_user" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn">Sign In</button>
            </form>

            <!-- Registration link -->
            <div class="link">
                <p>Don't have an account? <a href="register.php">Register now</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Carousel functionality
            const slides = document.querySelectorAll('.carousel-slide');
            const leftArrow = document.querySelector('.carousel-arrow-left');
            const rightArrow = document.querySelector('.carousel-arrow-right');
            const indicators = document.querySelectorAll('.indicator');
            
            let currentIndex = 0;
            const slideCount = slides.length;
            
            // Function to update carousel
            function updateCarousel() {
                // Remove active class from all slides
                slides.forEach(slide => slide.classList.remove('active'));
                
                // Add active class to current slide
                slides[currentIndex].classList.add('active');
                
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
            document.querySelector('.carousel').addEventListener('mouseenter', () => {
                clearInterval(intervalId);
            });
            
            // Resume auto-play when mouse leaves
            document.querySelector('.carousel').addEventListener('mouseleave', () => {
                intervalId = setInterval(nextSlide, 5000);
            });
        });
    </script>
</body>
</html>