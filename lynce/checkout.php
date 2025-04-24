<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance()->getConnection();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart_products = [];
$total_price = 0.0;

$placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
$stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($_SESSION['cart']));
$cart_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cart_products as $product) {
    $total_price += $product['price'] * $_SESSION['cart'][$product['id']];
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = clean_input($_POST['customer_name']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);

    if (empty($customer_name) || empty($phone) || empty($address)) {
        $errors[] = 'Please fill in all required fields.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $stmt_order = $db->prepare("INSERT INTO orders (customer_name, phone, address, product_id, status, created_at) VALUES (?, ?, ?, ?, 'pending', datetime('now'))");

            foreach ($cart_products as $product) {
                $quantity = $_SESSION['cart'][$product['id']];
                for ($i = 0; $i < $quantity; $i++) {
                    $stmt_order->execute([$customer_name, $phone, $address, $product['id']]);
                }
            }

            $db->commit();

            $_SESSION['cart'] = [];
            $success = true;
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'An error occurred while processing your order. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Checkout - LYNCE</title>
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

    <main class="order-form" style="margin-top: 100px;">
        <h2>Checkout</h2>

        <?php if ($success): ?>
            <div class="flash-message flash-success">
                Your order has been placed successfully! We will contact you shortly.
            </div>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h3>Order Summary</h3>
            <ul>
                <?php foreach ($cart_products as $product): ?>
                    <li><?php echo htmlspecialchars($product['name']); ?> (Size: <?php echo htmlspecialchars($product['size']); ?>) x <?php echo $_SESSION['cart'][$product['id']]; ?> - $<?php echo format_price($product['price'] * $_SESSION['cart'][$product['id']]); ?></li>
                <?php endforeach; ?>
            </ul>
            <h3>Total: $<?php echo format_price($total_price); ?></h3>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label for="customer_name" class="form-label">Name</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-input" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
