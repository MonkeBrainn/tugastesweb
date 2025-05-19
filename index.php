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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLux - Luxury Collection</title>
    <!-- Font awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- CSS already included via index.css -->
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <!-- Success Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <p>Product successfully added to cart!</p>
            <button class="btn" onclick="closeModal()">CONTINUE SHOPPING</button>
        </div>
    </div>

    <!-- Banner/Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>ReLux Luxury Collection</h1>
            <p>Discover our exquisite selection of pre-owned luxury items</p>
        </div>
    </div>

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
    <div class="product-container">
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($totalProducts === 0): ?>
            <p class="no-products">No products available at the moment.</p>
        <?php else: ?>
            <?php 
            $delay = 0;
            while ($row = mysqli_fetch_assoc($result)): 
                $delay++;
                // Format price with currency symbol
                $formatted_price = "$ " . number_format($row['harga'], 0, ',', '.');
            ?>
                <div class="card" style="--delay: <?php echo $delay; ?>">
                    <div class="img-container">
                        <img src="images/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                    </div>
                    <div class="content">
                        <h3><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <p class="price"><?php echo $formatted_price; ?></p>
                        <p class="desc">Premium quality authentic luxury item</p>
                         <a href="index.php?beli=<?php echo htmlspecialchars($row['idproduk']); ?>">
                            <button class="btn add-to-cart">ADD TO CART</button>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

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