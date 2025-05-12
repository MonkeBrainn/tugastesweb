<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil data sesi pengguna
$nama_user = isset($_SESSION["nama_user"]) ? htmlspecialchars($_SESSION["nama_user"]) : "Pengguna";
$foto_user = isset($_SESSION["foto_user"]) && !empty($_SESSION["foto_user"]) ? htmlspecialchars($_SESSION["foto_user"]) : "uploads/user.jpg";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online</title>
    
    <!-- Link to CSS file with cache-busting parameter -->
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header>
        <!-- Informasi toko -->
        <div class="store-info">
            <h1>ReBelle Bags</h1>
            <p>Luxury Secondhand, First-Class Quality</p>
        </div>
        <!-- Informasi pengguna -->
        <div class="user-info">
            <span>Halo, <?php echo $nama_user; ?></span>
            <img src="<?php echo $foto_user; ?>" alt="Foto Profil">
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="keranjang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'keranjang.php' ? 'active' : ''; ?>">Keranjang</a>
        <a href="riwayat.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : ''; ?>">Riwayat Belanja</a>
        <a href="setting.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'setting.php' ? 'active' : ''; ?>">Setting User</a>
        <a href="logout.php">Logout</a>
    </nav>

    <!-- Main content container -->
    <div class="main-content">
        <!-- Content will be injected here -->
        <div class="content">
            <!-- Your content goes here -->
        </div> 