<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'config/koneksi.php';

// Set page title
$page_title = "Bags Collection";

// Include header
include 'header.php';

// Get bag products from database
// Based on your database structure, we'll query bag products by their IDs or names containing "bag"
$query = "SELECT * FROM produk WHERE 
          idproduk IN ('p202504002', 'p202504003', 'p202504004', 'p202504005') OR 
          LOWER(nama_produk) LIKE '%bag%' OR 
          LOWER(nama_produk) LIKE '%boston%' OR
          LOWER(nama_produk) LIKE '%pochette%' OR
          LOWER(nama_produk) LIKE '%shoulder%'
          ORDER BY idproduk DESC";

$result = mysqli_query($koneksi, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($koneksi));
}

// Count the number of bag products
$totalBags = mysqli_num_rows($result);
?>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Bags Collection</h1>
        <p>Discover our exclusive selection of luxury bags</p>
    </div>
    
    <!-- Filter and Sort Options (Optional) -->
    <div class="filter-options">
        <div class="filter-container">
            <select id="sortOptions" onchange="sortProducts(this.value)">
                <option value="default">Sort By</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="newest">Newest Arrivals</option>
            </select>
        </div>
        
        <div class="results-count">
            <?php echo $totalBags; ?> products found
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="products-grid">
        <?php
        // Check if there are any bag products
        if ($totalBags > 0) {
            // Loop through each product
            while ($row = mysqli_fetch_assoc($result)) {
                // Format price with currency symbol
                $formatted_price = "Rp " . number_format($row['harga'], 0, ',', '.');
                
                // Product card
                echo '<div class="product-card">';
                
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
                
                // Add to cart button
                echo '<div class="product-actions">';
                echo '<a href="proses/tambah_keranjang.php?id=' . $row['idproduk'] . '" class="add-to-cart-btn">Add to Cart</a>';
                echo '</div>';
                
                echo '</div>'; // End product-info
                echo '</div>'; // End product-card
            }
        } else {
            // No bag products found
            echo '<div class="no-products">';
            echo '<p>No bag products are currently available.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<style>
    /* Container styles */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    /* Page header styles */
    .page-header {
        text-align: center;
        margin-bottom: 40px;
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
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
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
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }
    
    /* Product card styles */
    .product-card {
        border: 1px solid #eee;
        border-radius: 4px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .product-image {
        height: 300px;
        overflow: hidden;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.05);
    }
    
    .product-info {
        padding: 15px;
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
        margin-top: 15px;
    }
    
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
        // This is a placeholder - in a real implementation, you would:
        // 1. Either reload the page with a query parameter for sorting
        // 2. Or use AJAX to fetch and display sorted products
        
        // For now, we'll just reload with a sort parameter
        if (sortOption !== 'default') {
            window.location.href = 'bags.php?sort=' + sortOption;
        }
    }
</script>

<?php
// Include footer
include 'footer.php';
?>