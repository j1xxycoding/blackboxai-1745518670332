<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action'];

    if ($action === 'accept' || $action === 'decline') {
        $status = $action === 'accept' ? 'accepted' : 'declined';
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        set_flash_message('success', 'Order status updated successfully.');
        redirect('orders.php');
    } elseif ($action === 'clean' && verify_csrf_token($_POST['csrf_token'])) {
        $stmt = $db->prepare("DELETE FROM orders WHERE status = ?");
        $stmt->execute(['declined']);
        set_flash_message('success', 'Declined orders cleaned successfully.');
        redirect('orders.php');
    }
}

// Get specific order if ID is provided
$order = null;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("
        SELECT o.*, p.name as product_name, p.price, p.image, p.size 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.id = ?
    ");
    $stmt->execute([(int)$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get orders list with filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = $status_filter !== 'all' ? "WHERE o.status = ?" : "";

$stmt = $db->prepare("
    SELECT o.*, p.name as product_name, p.price 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    $where_clause 
    ORDER BY o.created_at DESC
");

if ($status_filter !== 'all') {
    $stmt->execute([$status_filter]);
} else {
    $stmt->execute();
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - LYNCE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="https://jaxxy.space/botop/logolynce.png" alt="LYNCE Logo">
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="products/list.php"><i class="fas fa-tshirt"></i> Products</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($flash = get_flash_message()): ?>
                <div class="flash-message flash-<?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <?php if ($order): ?>
                <!-- Single Order View -->
                <div class="page-header">
                    <h1>Order #<?php echo $order['id']; ?></h1>
                    <div class="header-actions">
                        <a href="orders.php" class="btn btn-primary">Back to Orders</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Order Details</div>
                    <div class="card-body">
                        <div class="order-details">
                            <div class="order-info">
                                <h3>Customer Information</h3>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                                <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Status:</strong> <?php echo get_status_badge($order['status']); ?></p>
                            </div>
                            <div class="product-info">
                                <h3>Product Information</h3>
                                <img src="<?php echo UPLOAD_URL . $order['image']; ?>" alt="Product Image" style="max-width: 200px;">
                                <p><strong>Product:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
                                <p><strong>Size:</strong> <?php echo htmlspecialchars($order['size']); ?></p>
                                <p><strong>Price:</strong> $<?php echo format_price($order['price']); ?></p>
                            </div>
                        </div>

                        <?php if ($order['status'] === 'pending'): ?>
                            <div class="order-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-success">Accept Order</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="action" value="decline">
                                    <button type="submit" class="btn btn-danger">Decline Order</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Orders List View -->
                <div class="page-header">
                    <h1>Orders Management</h1>
                    <div class="header-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="clean">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to clean all declined orders?')">
                                Clean Declined Orders
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="filter-bar">
                    <a href="?status=all" class="btn <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
                    <a href="?status=pending" class="btn <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
                    <a href="?status=accepted" class="btn <?php echo $status_filter === 'accepted' ? 'btn-primary' : 'btn-secondary'; ?>">Accepted</a>
                    <a href="?status=declined" class="btn <?php echo $status_filter === 'declined' ? 'btn-primary' : 'btn-secondary'; ?>">Declined</a>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                    <td>$<?php echo format_price($order['price']); ?></td>
                                    <td><?php echo get_status_badge($order['status']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="action" value="decline">
                                                <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
