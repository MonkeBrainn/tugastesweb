<?php
// Include header
include 'header.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Include connection if needed for featured products
require_once 'koneksi.php';

// Get featured products (you can modify this to get specific products)
function getFeaturedProducts($limit = 4) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get 4 random products to feature
        $sql = "SELECT idproduk, nama_produk, harga, gambar FROM produk ORDER BY RAND() LIMIT $limit";
        $result = $conn->query($sql);
        
        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        $db->closeConnection();
        return $products;
    } catch (Exception $e) {
        return [];
    }
}

$featuredProducts = getFeaturedProducts();
?>

<!-- Hero Banner Section -->
<div class="hero-banner">
    <div class="hero-content">
        <h1>Welcome to ReLux</h1>
        <p>Discover extraordinary luxury items at exceptional prices</p>
        <a href="index.php" class="btn">Shop Now</a>
    </div>
</div>

<!-- Featured Categories -->
<section class="featured-categories">
    <div class="section-title">
        <h2>Explore Our Collections</h2>
    </div>
    
    <div class="category-grid">
        <div class="category-card">
            <img src="images/category-outdoor.jpg" alt="Outdoor and Equestrian">
            <div class="category-info">
                <h3>Outdoor & Equestrian</h3>
                <a href="kategori.php?id=1" class="btn-outline">Explore</a>
            </div>
        </div>
        
        <div class="category-card">
            <img src="images/category-jewelry.jpg" alt="Jewelry and Watches">
            <div class="category-info">
                <h3>Jewelry & Watches</h3>
                <a href="kategori.php?id=2" class="btn-outline">Explore</a>
            </div>
        </div>
        
        <div class="category-card">
            <img src="images/category-fragrance.jpg" alt="Fragrances">
            <div class="category-info">
                <h3>Fragrances</h3>
                <a href="kategori.php?id=3" class="btn-outline">Explore</a>
            </div>
        </div>
        
        <div class="category-card">
            <img src="images/category-gifts.jpg" alt="Gifts">
            <div class="category-info">
                <h3>Gifts</h3>
                <a href="kategori.php?id=4" class="btn-outline">Explore</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <div class="section-title">
        <h2>Featured Products</h2>
        <p>Discover our specially curated selection of luxury items</p>
    </div>
    
    <div class="product-slider">
        <?php if (empty($featuredProducts)): ?>
            <p class="no-products">No featured products available.</p>
        <?php else: ?>
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <div class="img-container">
                        <img src="images/<?php echo htmlspecialchars($product['gambar']); ?>" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                    </div>
                    <div class="content">
                        <h3><?php echo htmlspecialchars($product['nama_produk']); ?></h3>
                        <p class="price">$ <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                        <a href="index.php?beli=<?php echo htmlspecialchars($product['idproduk']); ?>" class="btn-small">
                            View Product
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="view-all">
        <a href="index.php" class="btn">View All Products</a>
    </div>
</section>

<!-- Brand Promise Section -->
<section class="brand-promise">
    <div class="promise-container">
        <div class="promise-item">
            <i class="fas fa-check-circle"></i>
            <h3>Authenticity Guaranteed</h3>
            <p>All products are authentic and verified by our expert team</p>
        </div>
        
        <div class="promise-item">
            <i class="fas fa-shipping-fast"></i>
            <h3>Fast Shipping</h3>
            <p>Receive your order within 2-3 business days</p>
        </div>
        
        <div class="promise-item">
            <i class="fas fa-shield-alt"></i>
            <h3>Secure Transactions</h3>
            <p>Your payment information is always protected</p>
        </div>
        
        <div class="promise-item">
            <i class="fas fa-sync"></i>
            <h3>Easy Returns</h3>
            <p>30-day hassle-free return policy</p>
        </div>
    </div>
</section>

<!-- Include footer -->
<?php include 'footer.php'; ?>

<!-- CSS for home page -->
<style>
    /* Hero Banner */
    .hero-banner {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/hero-banner.jpg');
        background-size: cover;
        background-position: center;
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        margin-bottom: 60px;
    }
    
    .hero-content {
        max-width: 800px;
        padding: 0 20px;
    }
    
    .hero-content h1 {
        font-size: 3rem;
        margin-bottom: 15px;
        font-weight: 300;
        letter-spacing: 2px;
    }
    
    .hero-content p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        font-weight: 300;
    }
    
    .btn {
        display: inline-block;
        background-color: #333;
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        font-size: 16px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .btn:hover {
        background-color: #000;
    }
    
    .btn-outline {
        display: inline-block;
        background-color: transparent;
        color: #333;
        padding: 8px 20px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
        border: 1px solid #333;
    }
    
    .btn-outline:hover {
        background-color: #333;
        color: white;
    }
    
    .btn-small {
        display: inline-block;
        background-color: #333;
        color: white;
        padding: 8px 15px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    /* Section styles */
    section {
        padding: 60px 0;
    }
    
    .section-title {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .section-title h2 {
        font-size: 2rem;
        font-weight: 300;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .section-title p {
        color: #777;
    }
    
    /* Featured Categories */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .category-card {
        position: relative;
        overflow: hidden;
        height: 300px;
    }
    
    .category-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .category-card:hover img {
        transform: scale(1.05);
    }
    
    .category-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.9);
        padding: 15px;
        text-align: center;
    }
    
    .category-info h3 {
        margin-bottom: 10px;
        font-weight: 400;
    }
    
    /* Featured Products */
    .featured-products {
        background-color: #f9f9f9;
    }
    
    .product-slider {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .product-card {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .product-card .img-container {
        height: 200px;
        overflow: hidden;
    }
    
    .product-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-card .content {
        padding: 20px;
        text-align: center;
    }
    
    .product-card h3 {
        font-size: 1.1rem;
        margin-bottom: 10px;
        font-weight: 400;
    }
    
    .product-card .price {
        color: #333;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .view-all {
        text-align: center;
        margin-top: 40px;
    }
    
    /* Brand Promise */
    .brand-promise {
        background-color: white;
    }
    
    .promise-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .promise-item {
        text-align: center;
    }
    
    .promise-item i {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .promise-item h3 {
        font-size: 1.2rem;
        margin-bottom: 10px;
        font-weight: 400;
    }
    
    .promise-item p {
        color: #777;
        font-size: 0.9rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .category-grid,
        .product-slider,
        .promise-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .category-grid,
        .product-slider,
        .promise-container {
            grid-template-columns: 1fr;
        }
        
        .hero-content h1 {
            font-size: 2rem;
        }
    }
</style>

<!-- JavaScript for interactive elements -->
<script>
    // Add any JavaScript you need for the home page
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight active menu item
        const menuItems = document.querySelectorAll('.category-menu a');
        menuItems.forEach(item => {
            if(item.textContent.trim() === 'HOME') {
                item.classList.add('active');
            }
        });
    });
</script>