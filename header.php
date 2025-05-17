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
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        /* Top Bar Styles */
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background-color: #ffffff;
            border-bottom: 1px solid #eee;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            width: 200px;
        }
        
        .search-bar i {
            color: #555;
            margin-right: 5px;
        }
        
        .search-bar input {
            border: none;
            border-bottom: 1px solid #ddd;
            outline: none;
            padding: 5px;
            width: 100%;
            font-size: 14px;
        }
        
        .logo-container {
            text-align: center;
        }
        
        .logo-container img {
            height: 40px;
        }
        
        .user-actions {
            display: flex;
            gap: 20px;
            align-items: center;
            width: 200px;
            justify-content: flex-end;
        }
        
        .user-actions a {
            color: #333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Category Navigation */
        .category-menu {
            display: flex;
            justify-content: center;
            background-color: #ffffff;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .category-menu a {
            color: #333;
            text-decoration: none;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-right: 1px solid #eee;
        }
        
        .category-menu a:last-child {
            border-right: none;
        }
        
        .category-menu a:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar (Hermès Style) -->
    <div class="top-navbar">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search">
        </div>
        
        <div class="logo-container">
            <h1>ReLux</h1>
        </div>
        
        <div class="user-actions">
            <a href="setting.php">
                <i class="fas fa-user"></i> Account
            </a>
            <a href="keranjang.php">
                <i class="fas fa-shopping-bag"></i> Cart
            </a>
        </div>
    </div>
    
    <!-- Category Navigation -->
    <div class="category-menu">
        <a href="bags.php">BAGS</a>
        <a href="jewelry_watches.php">JEWELRY AND WATCHES</a>
        <a href="scarves.php">SCARVES</a>
        <a href="about.php">ABOUT</a>
    </div>

    <!-- Main content container -->
    <div class="main-content">
        <!-- Content will be injected here -->