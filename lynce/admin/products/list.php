<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login.php');
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $product_id = (int)$_POST['product_id'];
        
        // Get product image before deletion
        $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Delete product
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            // Delete product image
            if ($product && $product['image']) {
                $image_path = UPLOAD_PATH . $product['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            set_flash_message('success', 'Product deleted successfully.');
        } else {
            set_flash_message('error', 'Failed to delete product.');
        }
    }
    redirect('list.php');
}

// Get products list
$stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - LYNCE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="https://jaxxy.space/botop/logolynce.png" alt="LYNCE Logo">
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-tshirt"></i> Products</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($flash = get_flash_message()): ?>
                <div class="flash-message flash-<?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1>Products Management</h1>
                <div class="header-actions">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo UPLOAD_URL . htmlspecialchars($product['image']); ?>" 
                                         alt="Product Image" 
                                         style="max-width: 50px; height: auto;">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['size']); ?></td>
                                <td>$<?php echo format_price($product['price']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
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
