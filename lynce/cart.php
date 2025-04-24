<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance()->getConnection();

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    header('Location: cart.php');
    exit;
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header('Location: cart.php');
    exit;
}

// Fetch products in cart
$cart_products = [];
$total_price = 0.0;

if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $cart_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart_products as $product) {
        $total_price += $product['price'] * $_SESSION['cart'][$product['id']];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Shopping Cart - LYNCE</title>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <a href="index.php">
                    <img src="https://jaxxy.space/botop/logolynce.png" alt="LYNCE Logo" style="max-height: 40px;" />
                </a>
            </div>
        </nav>
    </header>

    <main class="products" style="margin-top: 100px;">
        <h2>Your Shopping Cart</h2>
        <?php if (empty($cart_products)): ?>
            <p>Your cart is empty.</p>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        <?php else: ?>
            <table class="admin-table" style="width: 100%; margin-bottom: 2rem;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['size']); ?></td>
                        <td>$<?php echo format_price($product['price']); ?></td>
                        <td><?php echo $_SESSION['cart'][$product['id']]; ?></td>
                        <td>$<?php echo format_price($product['price'] * $_SESSION['cart'][$product['id']]); ?></td>
                        <td><a href="cart.php?remove=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm">Remove</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Total: $<?php echo format_price($total_price); ?></h3>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        <?php endif; ?>
    </main>
</body>
</html>
