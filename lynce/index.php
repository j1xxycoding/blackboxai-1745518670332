<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get database connection
$db = Database::getInstance()->getConnection();

// Fetch featured products
$stmt = $db->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LYNCE - Modern Fashion Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/admin.js" defer></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="logo" style="margin-left: 1rem;">
                <a href="index.php">
                    <img src="https://jaxxy.space/botop/logolynce.png" alt="LYNCE Logo" style="max-height: 40px;">
                </a>
            </div>
            <div class="cart-link" style="margin-right: 1rem;">
                <a href="cart.php" class="btn btn-primary">Cart</a>
            </div>
        </nav>
    </header>

    <hr style="border: 1px solid black; margin: 0;">

    <!-- Hero Section -->
    <section class="hero modern-hero">
        <div class="hero-content">
            <img src="https://jaxxy.space/botop/whitelynce.png" alt="LYNCE Logo White" class="hero-logo">
            <p class="hero-subtitle">Discover the elegance of modern fashion</p>
            <a href="#featured" class="btn btn-primary hero-cta">Shop Now</a>
        </div>
    </section>

    <hr style="border: 1px solid black; margin: 0;">

    <!-- Featured Products -->
    <section class="products">
        <h2>Featured Collections</h2>
        <div class="products-grid" id="featured">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-image-container">
                    <img src="<?php echo UPLOAD_URL . htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-size">Size: <?php echo htmlspecialchars($product['size']); ?></p>
                    <p class="product-price">$<?php echo format_price($product['price']); ?></p>
                    <a href="products/product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Store Introduction -->
    <section class="store-intro">
        <div class="container">
            <h2>Welcome to LYNCE</h2>
            <p>Experience the perfect blend of style and comfort with our carefully curated collection. Each piece is selected to bring out your unique personality while maintaining the highest standards of quality.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> LYNCE. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
