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

// Get statistics
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'accepted_orders' => 0,
    'declined_orders' => 0,
    'total_products' => 0
];

// Get orders count
$stmt = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['status'] . '_orders'] = $row['count'];
    $stats['total_orders'] += $row['count'];
}

// Get products count
$stmt = $db->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent orders
$stmt = $db->query("
    SELECT o.*, p.name as product_name, p.price 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LYNCE</title>
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="products/list.php"><i class="fas fa-tshirt"></i> Products</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="card">
                    <div class="card-header">Total Orders</div>
                    <div class="card-body">
                        <h2><?php echo $stats['total_orders']; ?></h2>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Pending Orders</div>
                    <div class="card-body">
                        <h2><?php echo $stats['pending_orders']; ?></h2>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Accepted Orders</div>
                    <div class="card-body">
                        <h2><?php echo $stats['accepted_orders']; ?></h2>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Total Products</div>
                    <div class="card-body">
                        <h2><?php echo $stats['total_products']; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">Recent Orders</div>
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
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td>$<?php echo format_price($order['price']); ?></td>
                                <td><?php echo get_status_badge($order['status']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
