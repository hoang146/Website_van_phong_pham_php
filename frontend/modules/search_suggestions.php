<?php
// search_suggestions.php: Trả về gợi ý sản phẩm theo từ khóa (AJAX)
require_once '../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$suggestions = [];


if ($keyword !== '') {
    $stmt = $conn->prepare("SELECT p.id, p.name, (SELECT pi.image_path FROM product_images pi WHERE pi.product_id=p.id ORDER BY pi.id ASC LIMIT 1) AS image FROM products p WHERE p.name LIKE ? ORDER BY p.name LIMIT 8");
    $like = "%$keyword%";
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $image = $row['image'] ? '../../uploads/products/' . $row['image'] : '../../assets/img/no-image.png';
        $suggestions[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'image' => $image
        ];
    }
    $stmt->close();
}

echo json_encode($suggestions);