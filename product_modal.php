<?php
/**
 * Product Detail Modal Integration
 * 
 * This file provides functions for integrating the product image modal
 * into your e-commerce website.
 */

/**
 * Adds product modal scripts and styles to the header
 */
function add_product_modal_assets() {
    echo '<link rel="stylesheet" href="product_modal.css">';
    echo '<script src="product_modal.js" defer></script>';
}

/**
 * Wraps product images with proper classes for modal functionality
 * 
 * @param string $imagePath The path to the product image
 * @param string $productName The name of the product
 * @param string $productPrice The price of the product
 * @param string $additionalClasses Any additional CSS classes to add
 * @return string HTML for the product image with modal support
 */
function product_image_with_modal($imagePath, $productName, $productPrice, $additionalClasses = '') {
    $output = '
    <div class="product-item">
        <img src="' . htmlspecialchars($imagePath) . '" 
             alt="' . htmlspecialchars($productName) . '" 
             class="product-image ' . htmlspecialchars($additionalClasses) . '">
        <div class="product-name" style="display:none;">' . htmlspecialchars($productName) . '</div>
        <div class="product-price" style="display:none;">' . htmlspecialchars($productPrice) . '</div>
    </div>';
    
    return $output;
}

/**
 * Example of usage in a product listing:
 * 
 * <?php
 * include_once('product_modal.php');
 * add_product_modal_assets();
 * ?>
 * 
 * <div class="products-container">
 *   <?php
 *   // Loop through products
 *   foreach($products as $product) {
 *     echo product_image_with_modal(
 *       $product['image_path'],
 *       $product['name'],
 *       '$' . $product['price'],
 *       'thumbnail-class'
 *     );
 *     
 *     // Display product name and price below image
 *     echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
 *     echo '<p>$' . htmlspecialchars($product['price']) . '</p>';
 *     echo '<button>Add to Cart</button>';
 *   }
 *   ?>
 * </div>
 */
?>