<?php
// Function to display product cards
function displayProductCard($id, $name, $price, $image, $description) {
    $formattedPrice = " $ " . number_format($price, 0, ',', '.');
    ?>
    <div class="card">
        <div class="img-container">
            <?php if ($price > 1000000): ?>
                <span class="label new">Premium</span>
            <?php endif; ?>
            <button class="wishlist-icon">
                <i class="fa fa-heart"></i>
            </button>
            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="content">
            <h3><?php echo htmlspecialchars($name); ?></h3>
            <p class="price"><?php echo $formattedPrice; ?></p>
            <p class="desc"><?php echo htmlspecialchars($description); ?></p>
            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                <button type="submit" class="btn">Beli Sekarang</button>
            </form>
        </div>
    </div>
    <?php
}
?>