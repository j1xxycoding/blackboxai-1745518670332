<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_image'])) {
    $uploadDir = __DIR__ . '/../assets/uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file = $_FILES['product_image'];
    $targetFile = $uploadDir . basename($file['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        $uploadOk = 0;
        $message = "File is not an image.";
    }

    // Check file size (max 5MB)
    if ($file['size'] > 5000000) {
        $uploadOk = 0;
        $message = "Sorry, your file is too large.";
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
        $uploadOk = 0;
        $message = "Sorry, only JPG, JPEG, PNG files are allowed.";
    }

    if ($uploadOk && move_uploaded_file($file['tmp_name'], $targetFile)) {
        $message = "The file ". htmlspecialchars(basename($file['name'])) . " has been uploaded.";
    } else {
        if (!isset($message)) {
            $message = "Sorry, there was an error uploading your file.";
        }
    }
} else {
    $message = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Upload Product Images</title>
</head>
<body>
    <h1>Upload Product Images</h1>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="product_image">Select image to upload:</label>
        <input type="file" name="product_image" id="product_image" required>
        <button type="submit">Upload Image</button>
    </form>
</body>
</html>
