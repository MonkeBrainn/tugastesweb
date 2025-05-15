<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    header('Location: login.php?redirect=bags.php');
    exit();
}

// Include database connection
require_once 'koneksi.php';

// Initialize Database connection
try {
    $db = new Database();
    $koneksi = $db->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION['user_id'];
    $idproduk = isset($_POST['idproduk']) ? $_POST['idproduk'] : '';
    $harga = isset($_POST['harga']) ? $_POST['harga'] : 0;
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
    
    // Validate inputs
    if (empty($idproduk) || $harga <= 0 || $quantity <= 0) {
        header('Location: bags.php?error=invalid_input');
        exit();
    }
    
    // Check if product already exists in cart
    $check_query = "SELECT idkeranjang, quantity FROM dkeranjang 
                   WHERE id = ? AND idproduk = ? AND keterangan = 'Pending'";
    $stmt = $koneksi->prepare($check_query);
    $stmt->bind_param("is", $user_id, $idproduk);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Product already in cart, update quantity
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        $idkeranjang = $row['idkeranjang'];
        
        $update_query = "UPDATE dkeranjang SET quantity = ? WHERE idkeranjang = ?";
        $stmt = $koneksi->prepare($update_query);
        $stmt->bind_param("ii", $new_quantity, $idkeranjang);
        $stmt->execute();
    } else {
        // Product not in cart, add new item
        $insert_query = "INSERT INTO dkeranjang (id, idproduk, harga, quantity, keterangan) 
                        VALUES (?, ?, ?, ?, 'Pending')";
        $stmt = $koneksi->prepare($insert_query);
        $stmt->bind_param("isdi", $user_id, $idproduk, $harga, $quantity);
        $stmt->execute();
    }
    
    $stmt->close();
    $db->closeConnection();
    
    // Redirect back to bags page with success message
    header('Location: bags.php?cart=success');
    exit();
} else {
    // If not a POST request or add_to_cart not set
    header('Location: bags.php');
    exit();
}
?>