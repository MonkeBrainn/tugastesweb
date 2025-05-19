<?php
// Mulai sesi
session_start();

// Include database connection
require_once 'koneksi.php';

// Create database connection using the Database class
try {
    $db = new Database();
    $koneksi = $db->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Set page title
$page_title = "ReLux - Luxury Collection";

// Include header
include 'header.php';

// Combine all product IDs and keywords from all category pages
$query = "SELECT * FROM produk WHERE 
          /* Bags IDs */
          idproduk IN ('p202504002', 'p202504003', 'p202504004', 'p202504005', 
          'p202504006', 'p202504007', 'p202504008', 'p202504009', 'p202504010', 'p202504014',
          /* Jewelry & Watches IDs */
          'p202504016', 'p202504017', 'p202504018', 'p202504019', 'p202504020',
          /* Scarves IDs */
          'p202504022', 'p202504023', 'p202504024', 'p202504025') OR 
          /* Bags keywords */
          LOWER(nama_produk) LIKE '%bag%' OR 
          LOWER(nama_produk) LIKE '%boston%' OR
          LOWER(nama_produk) LIKE '%pochette%' OR
          LOWER(nama_produk) LIKE '%shoulder%' OR
          LOWER(nama_produk) LIKE '%tote%' OR
          LOWER(nama_produk) LIKE '%puzzle%' OR
          /* Jewelry & Watches keywords */
          LOWER(nama_produk) LIKE '%jewelry%' OR 
          LOWER(nama_produk) LIKE '%watch%' OR
          LOWER(nama_produk) LIKE '%bracelet%' OR
          LOWER(nama_produk) LIKE '%necklace%' OR
          LOWER(nama_produk) LIKE '%ring%' OR
          LOWER(nama_produk) LIKE '%earring%' OR
          /* Scarves keywords */
          LOWER(nama_produk) LIKE '%scarf%' OR 
          LOWER(nama_produk) LIKE '%scarves%' OR
          LOWER(nama_produk) LIKE '%shawl%' OR
          LOWER(nama_produk) LIKE '%silk%' OR
          LOWER(nama_produk) LIKE '%twilly%' OR
          LOWER(nama_produk) LIKE '%bandana%'";

// Handle sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// Add ORDER BY clause based on sort parameter
switch($sort) {
    case 'price-asc':
        $query .= " ORDER BY harga ASC";
        break;
    case 'price-desc':
        $query .= " ORDER BY harga DESC";
        break;
    case 'newest':
        $query .= " ORDER BY tanggal_tambah DESC"; // Assuming you have this column
        break;
    default:
        $query .= " ORDER BY idproduk DESC";
        break;
}

$result = mysqli_query($koneksi, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($koneksi));
}

// Count the number of products
$totalProducts = mysqli_num_rows($result);

// Inisialisasi variabel
$error = '';
$show_modal = false;

// Proses penambahan ke keranjang
if (isset($_GET['beli'])) {
    $id_produk = $_GET['beli'];
    $user_id = $_SESSION['user_id'];

    try {
        // Get product price
        $query_harga = $koneksi->prepare("SELECT idproduk, harga FROM produk WHERE idproduk = ?");
        $query_harga->bind_param("s", $id_produk);
        $query_harga->execute();
        $query_harga->bind_result($idProduk, $harga);
        $query_harga->fetch();
        $query_harga->close();

        if (!$harga) {
            throw new Exception("Product not found.");
        }

        // Check if product already in cart with "Pending" status
        $cek = $koneksi->prepare("SELECT idkeranjang, quantity FROM dkeranjang WHERE idproduk = ? AND harga = ? AND id = ? AND keterangan = 'Pending'");
        $cek->bind_param("sis", $id_produk, $harga, $user_id);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            // Update quantity if product already exists
            $cek->bind_result($idkeranjang, $quantity);
            $cek->fetch();
            $new_quantity = $quantity + 1;
            $update = $koneksi->prepare("UPDATE dkeranjang SET quantity = ? WHERE idkeranjang = ?");
            $update->bind_param("ii", $new_quantity, $idkeranjang);
            $update->execute();
            $update->close();
        } else {
            // Insert new product to cart
            $insert = $koneksi->prepare("INSERT INTO dkeranjang (idproduk, harga, quantity, keterangan, id) VALUES (?, ?, 1, 'Pending', ?)");
            $insert->bind_param("sis", $id_produk, $harga, $user_id);
            $insert->execute();
            $insert->close();
        }
        $cek->close();
        $show_modal = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// For Bags Collection - using one of your bag product IDs
$bags_query = "SELECT * FROM produk WHERE 
              idproduk = 'p202504002' 
              LIMIT 1";
$bags_result = mysqli_query($koneksi, $bags_query);
$bags_image = "bags_collection.png"; // Default image if query fails
if (mysqli_num_rows($bags_result) > 0) {
    $bags_row = mysqli_fetch_assoc($bags_result);
    if (!empty($bags_row['gambar'])) {
        $bags_image = $bags_row['gambar'];
    }
}

// For Scarves Collection - using one of your scarves product IDs
$scarves_query = "SELECT * FROM produk WHERE 
                idproduk = 'p202504022' 
                LIMIT 1";
$scarves_result = mysqli_query($koneksi, $scarves_query);
$scarves_image = "scarves_collection.png"; // Default image if query fails
if (mysqli_num_rows($scarves_result) > 0) {
    $scarves_row = mysqli_fetch_assoc($scarves_result);
    if (!empty($scarves_row['gambar'])) {
        $scarves_image = $scarves_row['gambar'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Font awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Base styles */
        :root {
            --primary-font: 'Helvetica Neue', Arial, sans-serif;
            --secondary-font: 'Times New Roman', Times, serif;
            --text-color: #333;
            --light-text: #fff;
            --button-color: #000;
            --button-text: #fff;
            --transition-speed: 0.3s;
        }

        body {
            font-family: var(--primary-font);
            margin: 0;
            padding: 0;
            color: var(--text-color);
            background-color: #fff;
            overflow-x: hidden;
        }

        /* Video Hero Section */
        .video-hero {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        .video-hero video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            bottom: 5%;
            width: 100%;
            text-align: center;
            color: var(--light-text);
            text-transform: uppercase;
            letter-spacing: 5px;
        }

        .hero-overlay h1 {
            font-size: 2.5rem;
            font-weight: 400;
            margin-bottom: 1rem;
            letter-spacing: 10px;
        }

        .hero-cta {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 30px;
            border: 1px solid white;
            text-decoration: none;
            letter-spacing: 2px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }

        .hero-cta:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        /* Section Headers */
        .section-title {
            text-align: center;
            padding: 60px 0 30px;
            font-size: 1.8rem;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* Collection Categories */
        .collection-categories {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            margin: 0 auto;
            max-width: 1400px;
            padding: 0 20px;
        }

        .category-item {
            position: relative;
            overflow: hidden;
            height: 70vh;
        }

        .category-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s ease;
        }

        .category-item:hover .category-image {
            transform: scale(1.05);
        }

        .category-overlay {
            position: absolute;
            bottom: 40px;
            left: 0;
            width: 100%;
            text-align: center;
            color: white;
        }

        .category-title {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 15px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .shop-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            letter-spacing: 1px;
            border-bottom: 1px solid white;
            padding-bottom: 2px;
            text-transform: uppercase;
        }

        /* Featured Products */
        .featured-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-card {
            text-align: center;
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 350px;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .product-name {
            font-size: 1rem;
            font-weight: 400;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }

        .add-to-cart {
            background-color: var(--button-color);
            color: var(--button-text);
            border: none;
            padding: 10px 25px;
            font-size: 0.8rem;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-transform: uppercase;
        }

        .add-to-cart:hover {
            background-color: #333;
        }

        /* All Products Section */
        .all-products-section {
            background-color: #f8f8f8;
            padding: 40px 0;
        }

        .filter-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto 30px;
            padding: 0 20px;
        }

        .filter-container select {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 10px 35px 10px 15px;
            font-size: 0.9rem;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 10px auto;
            min-width: 200px;
        }

        .results-count {
            color: #666;
            font-size: 0.9rem;
        }

        .all-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Success Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background-color: #fff;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: modalFadeIn 0.3s;
        }

        .modal-content p {
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .modal-btn {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 12px 25px;
            cursor: pointer;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .collection-categories {
                grid-template-columns: 1fr;
            }

            .category-item {
                height: 50vh;
            }

            .video-hero {
                height: 60vh;
            }

            .hero-overlay h1 {
                font-size: 1.5rem;
            }

            .section-title {
                padding: 40px 0 20px;
                font-size: 1.5rem;
            }

            .filter-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Success Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <p>Product successfully added to cart!</p>
            <button class="modal-btn" onclick="closeModal()">CONTINUE SHOPPING</button>
        </div>
    </div>

    <!-- Video Hero Section -->
    <section class="video-hero">
        <video autoplay muted loop playsinline>
            <source src="images/luxury_fashion.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-overlay">
            <h1>ReLux Spring Opening 2025</h1>
            <a href="#featured" class="hero-cta">Discover Now</a>
        </div>
    </section>

    <!-- Collection Categories Section -->
    <h2 class="section-title">Our Collections</h2>
    <div class="collection-categories">
        <div class="category-item">
            <img src="images/<?php echo htmlspecialchars($bags_image); ?>" alt="Bags Collection" class="category-image">
            <div class="category-overlay">
                <h3 class="category-title">Bags collection</h3>
                <a href="mens.php" class="shop-link">Shop Now</a>
            </div>
        </div>
        <div class="category-item">
            <img src="images/<?php echo htmlspecialchars($scarves_image); ?>" alt="Scarves Collection" class="category-image">
            <div class="category-overlay">
                <h3 class="category-title">Scarves Collection</h3>
                <a href="womens.php" class="shop-link">Shop Now</a>
            </div>
        </div>
    </div>

    <!-- Featured Products Section -->
    <h2 class="section-title" id="featured">Featured Products</h2>
    <div class="featured-products">
        <?php
        // Display only the first 8 products as featured
        $featured_count = 0;
        mysqli_data_seek($result, 0); // Reset the result pointer
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($featured_count >= 8) break; // Limit to 8 featured products
            
            $featured_count++;
            $formatted_price = "$ " . number_format($row['harga'], 0, ',', '.');
        ?>
            <div class="product-card">
                <img src="images/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" class="product-image">
                <h3 class="product-name"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                <p class="product-price"><?php echo $formatted_price; ?></p>
                <a href="index.php?beli=<?php echo htmlspecialchars($row['idproduk']); ?>">
                    <button class="add-to-cart">Add to Cart</button>
                </a>
            </div>
        <?php } ?>
    </div>

    <!-- All Products Section -->
    <section class="all-products-section">
        <h2 class="section-title">All Products</h2>
        
        <!-- Filter and Sort Options -->
        <div class="filter-options">
            <div class="filter-container">
                <select id="sortOptions" onchange="sortProducts(this.value)">
                    <option value="default" <?php if($sort == 'default') echo 'selected'; ?>>Sort By</option>
                    <option value="price-asc" <?php if($sort == 'price-asc') echo 'selected'; ?>>Price: Low to High</option>
                    <option value="price-desc" <?php if($sort == 'price-desc') echo 'selected'; ?>>Price: High to Low</option>
                    <option value="newest" <?php if($sort == 'newest') echo 'selected'; ?>>Newest Arrivals</option>
                </select>
            </div>
            
            <div class="results-count">
                <?php echo $totalProducts; ?> products found
            </div>
        </div>
        
        <!-- Product Showcase -->
        <div class="all-products">
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($totalProducts === 0): ?>
                <p class="no-products">No products available at the moment.</p>
            <?php else: ?>
                <?php 
                mysqli_data_seek($result, 0); // Reset the result pointer
                while ($row = mysqli_fetch_assoc($result)): 
                    $formatted_price = "$ " . number_format($row['harga'], 0, ',', '.');
                ?>
                    <div class="product-card">
                        <img src="images/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" class="product-image">
                        <h3 class="product-name"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <p class="product-price"><?php echo $formatted_price; ?></p>
                        <a href="index.php?beli=<?php echo htmlspecialchars($row['idproduk']); ?>">
                            <button class="add-to-cart">Add to Cart</button>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Include footer -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script>
        // Function to sort products
        function sortProducts(sortOption) {
            window.location.href = 'index.php?sort=' + sortOption;
        }

        // Function to show modal
        function showModal() {
            document.getElementById('myModal').classList.add('show');
        }

        // Function to hide modal
        function closeModal() {
            document.getElementById('myModal').classList.remove('show');
        }

        // Show modal if data was successfully saved
        window.onload = function(){
            <?php if ($show_modal): ?>
                showModal();
            <?php endif; ?>
            
            // Smooth scroll for internal links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        };
    </script>
</body>
</html>

<?php
// Close the database connection when done
if (isset($db)) {
    $db->closeConnection();
}
?>