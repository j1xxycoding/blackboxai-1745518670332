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

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    set_flash_message('error', 'Product not found.');
    redirect('list.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        // Validate and sanitize input
        $name = clean_input($_POST['name']);
        $size = clean_input($_POST['size']);
        $price = (float)$_POST['price'];
        $description = clean_input($_POST['description']);
        $errors = [];

        // Validate required fields
        if (empty($name)) $errors[] = "Product name is required.";
        if (empty($size)) $errors[] = "Size is required.";
        if (empty($price)) $errors[] = "Price is required.";
        if ($price <= 0) $errors[] = "Price must be greater than 0.";

        // Handle image upload if new image is provided
        $image_filename = $product['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_image($_FILES['image']);
            if (!$upload_result['success']) {
                $errors[] = $upload_result['message'];
            } else {
                // Delete old image
                if ($product['image']) {
                    $old_image_path = UPLOAD_PATH . $product['image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $image_filename = $upload_result['filename'];
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    UPDATE products 
                    SET name = ?, size = ?, price = ?, description = ?, image = ? 
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$name, $size, $price, $description, $image_filename, $product_id])) {
                    set_flash_message('success', 'Product updated successfully.');
                    redirect('list.php');
                } else {
                    $errors[] = "Failed to update product.";
                }
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $errors[] = "Invalid form submission.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - LYNCE</title>
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
            <div class="page-header">
                <h1>Edit Product</h1>
                <div class="header-actions">
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="form-row">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label for="size" class="form-label">Size</label>
                        <input type="text" id="size" name="size" class="form-input" 
                               value="<?php echo htmlspecialchars($product['size']); ?>" required>
                        <small>Example: S, M, L, XL or specific measurements</small>
                    </div>

                    <div class="form-row">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" id="price" name="price" class="form-input" step="0.01" min="0" 
                               value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-input" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" id="image" name="image" class="form-input" accept="image/*">
                        <small>Leave empty to keep current image. Accepted formats: JPG, JPEG, PNG. Max size: 5MB</small>
                    </div>

                    <div class="form-row">
                        <div id="current-image">
                            <p>Current Image:</p>
                            <img src="<?php echo UPLOAD_URL . htmlspecialchars($product['image']); ?>" 
                                 alt="Current Product Image" 
                                 style="max-width: 200px;">
                        </div>
                        <div id="image-preview"></div>
                    </div>

                    <div class="form-row">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.style.maxWidth = '200px';
                    img.style.marginTop = '10px';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>
