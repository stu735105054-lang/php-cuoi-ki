<?php
require_once 'config.php';
try {
    $pdo = new PDO("mysql:host=localhost;dbname=smart_notes;charset=utf8mb4", "root", "");  // Kết nối DB
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Bắt lỗi
} catch(Exception $e) {
    echo "Lỗi kết nối DB: " . $e->getMessage();  // Debug cho sinh viên
    die();  // Dừng script nếu lỗi
}
?>