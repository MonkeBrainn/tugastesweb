<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
$page_title = "Bags Collection";

// Include header
include 'header.php';

// Include product modal functionality
include_once('product_modal.php');
add_product_modal_assets();

// Get bag products from database with the fixed query
$query = "SELECT * FROM produk WHERE 
          idproduk IN ('p202504002', 'p202504003', 'p202504004', 'p202504005', 
          'p202504006', 'p202504007', 'p202504008', 'p202504009', 'p202504010', 'p202504014') OR 
          LOWER(nama_produk) LIKE '%bag%' OR 
          LOWER(nama_produk) LIKE '%boston%' OR
          LOWER(nama_produk) LIKE '%pochette%' OR
          LOWER(nama_produk) LIKE '%shoulder%' OR
          LOWER(nama_produk) LIKE '%tote%' OR
          LOWER(nama_produk) LIKE '%puzzle%'";

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

// Count the number of bag products
$totalBags = mysqli_num_rows($result);
?>

<!-- Full-width container for the bags collection -->
<div class="bags-full-width">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Bags Collection</h1>
        <p>Discover our available Secondhand luxury bags</p>
    </div>
    
    <!-- Filter and Sort Options (Optional) -->
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
                $formatted_price = "$ " . number_format($row['harga'], 0, ',', '.');
                
                // Product card
                echo '<div class="product-card product-item">';
                
                // Product image - Modified to work with modal
                echo '<div class="product-image">';
                echo '<a href="detail_produk.php?id=' . $row['idproduk'] . '">';
                echo '<img src="images/' . $row['gambar'] . '" alt="' . $row['nama_produk'] . '" class="product-image-img">';
                echo '</a>';
                echo '</div>';
                
                // Add hidden product details for modal
                echo '<div class="product-name" style="display:none;">' . $row['nama_produk'] . '</div>';
                echo '<div class="product-price" style="display:none;">' . $formatted_price . '</div>';
                
                // Product info
                echo '<div class="product-info">';
                echo '<h3><a href="detail_produk.php?id=' . $row['idproduk'] . '">' . $row['nama_produk'] . '</a></h3>';
                echo '<p class="product-price">' . $formatted_price . '</p>';
                echo '</div>'; // End product-info
                
                // Add to cart button - now positioned absolutely via CSS
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
            // No bag products found
            echo '<div class="no-products">';
            echo '<p>No bag products are currently available.</p>';
            echo '</div>';
        }
        
        // Close the database connection when done
        if (isset($db)) {
            $db->closeConnection();
        }
        ?>
    </div>
</div>

<style>
    /* Full-width container styles */
    .bags-full-width {
        width: 100%;
        padding: 40px 0;
        background-color: #fff;
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

    /* Product card styles - Using the updated styles for fixed button height */
    .product-card {
        border: 1px solid #eee;
        border-radius: 4px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: #fff;
        position: relative; /* Added for positioning context */
        padding-bottom: 60px; /* Added space for the button at bottom */
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .product-image {
        height: 300px;
        overflow: hidden;
        position: relative;
        cursor: pointer; /* Add cursor pointer to indicate clickable */
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
        flex-grow: 1; /* Allow the info section to grow */
        display: flex;
        flex-direction: column;
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
        position: absolute; /* Position at the bottom */
        bottom: 15px;
        left: 15px;
        right: 15px;
        margin-top: auto; /* Push to bottom */
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
    
    /* Modal styles - Add these to support the product modal */
    .product-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 50px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.8);
    }
    
    .modal-content {
        position: relative;
        margin: auto;
        padding: 0;
        width: 80%;
        max-width: 1000px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .modal-image-container {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    #modalImage {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
    }
    
    .close-modal {
        position: absolute;
        top: 10px;
        right: 25px;
        color: white;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1001;
    }
    
    .nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        padding: 16px;
        color: white;
        font-weight: bold;
        font-size: 24px;
        cursor: pointer;
        background-color: rgba(0,0,0,0.3);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        transition: background-color 0.3s ease;
    }
    
    .prev-btn {
        left: 20px;
    }
    
    .next-btn {
        right: 20px;
    }
    
    .nav-btn:hover {
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-product-info {
        margin-top: 20px;
        text-align: center;
        color: white;
        padding: 0 20px 20px 20px;
    }
    
    .modal-product-info h3 {
        font-size: 24px;
        margin-bottom: 10px;
    }
    
    .modal-price {
        font-size: 20px;
        font-weight: 600;
    }
</style>

<script>
    // Function to sort products
    function sortProducts(sortOption) {
        window.location.href = 'bags.php?sort=' + sortOption;
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
        
        // Store all products for navigation
        const allProducts = [];
        
        // Collect all products for navigation
        document.querySelectorAll('.product-item').forEach(function(productCard) {
            const productData = {
                name: productCard.querySelector('.product-name').textContent,
                price: productCard.querySelector('.product-price').textContent,
                image: productCard.querySelector('img').src,
                id: productCard.querySelector('input[name="idproduk"]').value
            };
            allProducts.push(productData);
        });
        
        // Set global variable for product navigation
        window.allBagProducts = allProducts;
        window.currentProductIndex = 0;
        
        // Add click handlers to product images for modal functionality
        document.querySelectorAll('.product-image').forEach(function(imageContainer, index) {
            imageContainer.addEventListener('click', function(e) {
                // Only trigger modal if click is on the image or its container (not the link)
                if (e.target === this || e.target.classList.contains('product-image-img')) {
                    e.preventDefault();
                    
                    // Store current product index for navigation
                    window.currentProductIndex = index;
                    
                    // Create modal if it doesn't exist
                    if (!document.getElementById('productModal')) {
                        createProductModal();
                    }
                    
                    // Display the current product in modal
                    showProductInModal(window.currentProductIndex);
                    
                    // Show modal
                    document.getElementById('productModal').style.display = 'block';
                }
            });
        });
    });
    
    // Function to create product modal
    function createProductModal() {
        const modal = document.createElement('div');
        modal.id = 'productModal';
        modal.className = 'product-modal';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        
        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-modal';
        closeBtn.innerHTML = '&times;';
        
        // Add left navigation button
        const leftNavBtn = document.createElement('div');
        leftNavBtn.className = 'nav-btn prev-btn';
        leftNavBtn.innerHTML = '&#10094;'; // Left arrow
        
        // Add right navigation button
        const rightNavBtn = document.createElement('div');
        rightNavBtn.className = 'nav-btn next-btn';
        rightNavBtn.innerHTML = '&#10095;'; // Right arrow
        
        const modalImgContainer = document.createElement('div');
        modalImgContainer.className = 'modal-image-container';
        
        const modalImg = document.createElement('img');
        modalImg.id = 'modalImage';
        
        const productInfo = document.createElement('div');
        productInfo.className = 'modal-product-info';
        
        modalImgContainer.appendChild(modalImg);
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(leftNavBtn);
        modalContent.appendChild(modalImgContainer);
        modalContent.appendChild(rightNavBtn);
        modalContent.appendChild(productInfo);
        modal.appendChild(modalContent);
        
        document.body.appendChild(modal);
        
        // Close modal when clicking the X
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking outside the image
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
        
        // Navigation button functionality
        leftNavBtn.addEventListener('click', function() {
            navigateImages('prev');
        });
        
        rightNavBtn.addEventListener('click', function() {
            navigateImages('next');
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(event) {
            if (modal.style.display === 'block') {
                if (event.key === 'ArrowLeft') {
                    navigateImages('prev');
                } else if (event.key === 'ArrowRight') {
                    navigateImages('next');
                } else if (event.key === 'Escape') {
                    modal.style.display = 'none';
                }
            }
        });
    }
    
    // Function to navigate between products
    function navigateImages(direction) {
        if (window.allBagProducts && window.allBagProducts.length > 1) {
            if (direction === 'next') {
                window.currentProductIndex = (window.currentProductIndex + 1) % window.allBagProducts.length;
            } else {
                window.currentProductIndex = (window.currentProductIndex - 1 + window.allBagProducts.length) % window.allBagProducts.length;
            }
            
            // Show the new product
            showProductInModal(window.currentProductIndex);
        }
    }
    
    // Function to display a product in the modal
    function showProductInModal(index) {
        const product = window.allBagProducts[index];
        const modalImg = document.getElementById('modalImage');
        const productInfo = document.querySelector('.modal-product-info');
        
        // Update image and product info
        modalImg.src = product.image;
        productInfo.innerHTML = `
            <h3>${product.name}</h3>
            <p class="modal-price">${product.price}</p>
            <a href="detail_produk.php?id=${product.id}" class="view-details-btn">View Details</a>
        `;
    }
</script>

<?php
// Include footer
include 'footer.php';
?>