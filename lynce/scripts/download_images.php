<?php
$images = [
    'classic-white-shirt.jpg' => 'https://via.placeholder.com/300x400?text=Classic+White+Shirt',
    'black-leather-jacket.jpg' => 'https://via.placeholder.com/300x400?text=Black+Leather+Jacket',
    'elegant-evening-gown.jpg' => 'https://via.placeholder.com/300x400?text=Elegant+Evening+Gown',
    'casual-denim-jeans.jpg' => 'https://via.placeholder.com/300x400?text=Casual+Denim+Jeans',
    'sporty-sneakers.jpg' => 'https://via.placeholder.com/300x400?text=Sporty+Sneakers',
];

$uploadDir = __DIR__ . '/../assets/uploads/products/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

foreach ($images as $filename => $url) {
    $filePath = $uploadDir . $filename;
    if (!file_exists($filePath)) {
        file_put_contents($filePath, file_get_contents($url));
        echo "Downloaded $filename\n";
    } else {
        echo "$filename already exists\n";
    }
}
echo "Image download complete.\n";
