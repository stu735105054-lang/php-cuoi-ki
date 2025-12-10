<?php
require_once 'config.php';  // Load config trước để dùng constant nếu cần

try {
    // Kết nối MySQL với PDO (host localhost, db smart_notes, user root, pass rỗng)
    $pdo = new PDO("mysql:host=localhost;dbname=smart_notes;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Bật chế độ báo lỗi chi tiết
} catch(Exception $e) {
    echo "Lỗi kết nối DB: " . $e->getMessage();  // Hiển thị lỗi để debug
    die();  // Dừng script nếu không kết nối được
}
?>