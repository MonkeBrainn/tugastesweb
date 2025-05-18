<?php
// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include connection
require_once 'koneksi.php';

// Create database connection using Database class
try {
    $db = new Database();
    $koneksi = $db->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Page title
$page_title = "Jewelry & Watches Collection";

// Include header
include 'header.php';

// Get jewelry and watches products from database with specific details
$query = "SELECT * FROM produk WHERE 
          idproduk IN ('p202504016', 'p202504017', 'p202504018', 'p202504019', 'p202504020') OR 
          LOWER(nama_produk) LIKE '%jewelry%' OR 
          LOWER(nama_produk) LIKE '%watch%' OR
          LOWER(nama_produk) LIKE '%bracelet%' OR
          LOWER(nama_produk) LIKE '%necklace%' OR
          LOWER(nama_produk) LIKE '%ring%' OR
          LOWER(nama_produk) LIKE '%earring%'";

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

// Count the number of jewelry and watches products
$totalItems = mysqli_num_rows($result);
?>

<!-- Full-width container for the jewelry & watches collection -->
<div class="jewelry-watches-full-width">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Jewelry & Watches Collection</h1>
        <p>Discover our exquisite selection of secondhand luxury jewelry and timepieces</p>
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
            <?php echo $totalItems; ?> products found
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="products-grid">
        <?php
        // Check if there are any jewelry or watches products
        if ($totalItems > 0) {
            // Loop through each product
            while ($row = mysqli_fetch_assoc($result)) {
                // Format price with currency symbol
                $formatted_price = "$ " . number_format($row['harga'], 0, ',', '.');
                
                // Product card
                echo '<div class="product-card product-item">';
                
                // Product image
                echo '<div class="product-image">';
                echo '<a href="detail_produk.php?id=' . $row['idproduk'] . '">';
                echo '<img src="images/' . $row['gambar'] . '" alt="' . $row['nama_produk'] . '">';
                echo '</a>';
                echo '</div>';
                
                // Product info
                echo '<div class="product-info">';
                echo '<h3><a href="detail_produk.php?id=' . $row['idproduk'] . '">' . $row['nama_produk'] . '</a></h3>';
                echo '<p class="product-price">' . $formatted_price . '</p>';
                echo '</div>'; // End product-info
                
                // Add to cart button
                echo '<div class="product-actions">';
                echo '<form method="POST" action="tambah_keranjang.php">';
                echo '<input type="hidden" name="idproduk" value="' . $row['idproduk'] . '">';
                echo '<input type="hidden" name="nama_produk" value="' . $row['nama_produk'] . '">';
                echo '<input type="hidden" name="harga" value="' . $row['harga'] . '">';
                echo '<input type="hidden" name="gambar" value="' . $row['gambar'] . '">';
                echo '<input type="hidden" name="quantity" value="1">';
                echo '<button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>';
                echo '</form>';
                echo '</div>';
                
                echo '</div>'; // End product-card
            }
        } else {
            // No jewelry or watches products found
            echo '<div class="no-products">';
            echo '<p>No jewelry or watches products are currently available.</p>';
            echo '</div>';
        }
        
        // Close the database connection when done
        if (isset($db)) {
            $db->closeConnection();
        }
        ?>
    </div>
</div>

<!-- Add the stylesheet for the carousel -->
<link rel="stylesheet" href="jewelry_carousel.css">

<!-- Add the JavaScript for the carousel -->
<script src="jewelry_carousel.js"></script>

<style>
    /* Full-width container styles */
    .jewelry-watches-full-width {
        width: 100%;
        padding: 40px 0;
        background-color: #FAFAFA;
    }
    
    /* Page header styles */
    .page-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 0 20px;
    }
    
    .page-header h1 {
        font-size: 32px;
        font-weight: 500;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }
    
    .page-header p {
        font-size: 16px;
        color: #666;
    }
    
    /* Filter options styles */
    .filter-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 0 20px 20px 20px;
        border-bottom: 1px solid #eee;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .filter-container select {
        padding: 8px 15px;
        border: 1px solid #ddd;
        background-color: #fff;
        font-size: 14px;
        cursor: pointer;
    }
    
    .results-count {
        font-size: 14px;
        color: #666;
    }
    
    /* Products grid styles */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 30px;
        padding: 10px 20px 30px 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    @media (min-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 992px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1200px) {
        .products-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    /* Product card styles */
    .product-card {
        border: 1px solid #eee;
        border-radius: 4px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: #fff;
        position: relative;
        padding-bottom: 60px;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .product-image {
        height: 250px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.5s ease;
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.05);
    }
    
    .product-info {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        margin-bottom: 60px;
    }
    
    .product-info h3 {
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 10px;
    }
    
    .product-info h3 a {
        color: #333;
        text-decoration: none;
    }
    
    .product-price {
        font-size: 15px;
        font-weight: 600;
        color: #000;
        margin-bottom: 15px;
    }
    
    .product-actions {
        position: absolute;
        bottom: 15px;
        left: 15px;
        right: 15px;
        margin-top: auto;
    }
    
    /* Success message styles */
    .cart-success-message {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        transition: opacity 0.5s ease;
    }
    
    /* Add to cart button styles */
    .add-to-cart-btn {
        display: inline-block;
        width: 100%;
        padding: 10px 0;
        background-color: #000;
        color: #fff;
        text-align: center;
        text-decoration: none;
        font-size: 14px;
        border-radius: 2px;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .add-to-cart-btn:hover {
        background-color: #333;
    }
    
    /* No products message */
    .no-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 0;
    }
    
    .no-products p {
        font-size: 16px;
        color: #666;
    }
</style>

<script>
    // Function to sort products
    function sortProducts(sortOption) {
        window.location.href = 'jewelry_watches.php?sort=' + sortOption;
    }
    
    // Add to cart success message
    document.addEventListener('DOMContentLoaded', function() {
        // Check for URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const cartStatus = urlParams.get('cart');
        
        if (cartStatus === 'success') {
            // Create success message
            const successMessage = document.createElement('div');
            successMessage.className = 'cart-success-message';
            successMessage.innerHTML = 'Item added to cart successfully!';
            
            // Add to page
            document.querySelector('.page-header').appendChild(successMessage);
            
            // Remove after 3 seconds
            setTimeout(function() {
                successMessage.style.opacity = '0';
                setTimeout(function() {
                    successMessage.remove();
                }, 500);
            }, 3000);
        }
    });
</script>

<?php
// Include footer
include 'footer.php';
?>