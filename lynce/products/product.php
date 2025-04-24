<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get database connection
$db = Database::getInstance()->getConnection();

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// If product not found, redirect to homepage
if (!$product) {
    redirect('../index.php');
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('error', 'Invalid request.');
        redirect($_SERVER['PHP_SELF'] . '?id=' . $product_id);
    }

    // Validate and sanitize input
    $customer_name = clean_input($_POST['customer_name']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);

    // Basic validation
    if (empty($customer_name) || empty($phone) || empty($address)) {
        set_flash_message('error', 'All fields are required.');
    } else {
        try {
            // Insert order into database
            $stmt = $db->prepare("INSERT INTO orders (customer_name, phone, address, product_id, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$customer_name, $phone, $address, $product_id]);
            
            set_flash_message('success', 'Your order has been placed successfully! We will contact you shortly.');
            redirect($_SERVER['PHP_SELF'] . '?id=' . $product_id);
        } catch (PDOException $e) {
            set_flash_message('error', 'An error occurred while placing your order. Please try again.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - LYNCE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <a href="../index.php">
                    <img src="https://jaxxy.space/botop/logolynce.png" alt="LYNCE Logo" class="product-logo">
                </a>
            </div>
        </nav>
    </header>

    <!-- Product Details -->
    <div class="product-detail">
        <div class="container">
            <?php if ($flash = get_flash_message()): ?>
                <div class="flash-message flash-<?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

                <div class="product-content">
                    <div class="product-image-container">
                        <img src="<?php echo UPLOAD_URL . htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    </div>
                    <div class="product-info">
                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="product-size">Size: <?php echo htmlspecialchars($product['size']); ?></p>
                        <p class="product-price">$<?php echo format_price($product['price']); ?></p>
                        <p class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        
                        <!-- Add to Cart Form -->
                        <form method="POST" action="../cart.php" class="order-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                             
                            <div class="form-group">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-input" value="1" min="1" required>
                            </div>
    
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> LYNCE. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
