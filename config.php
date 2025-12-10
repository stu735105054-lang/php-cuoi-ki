<?php
session_start();  // Bắt đầu session để lưu thông tin user (user_id, name, is_admin)

define('UPLOAD_DIR', __DIR__ . '/uploads/');  // Đường dẫn thư mục upload file (tạo folder uploads nếu chưa có)
define('UPLOAD_URL', './uploads/');  // URL để hiển thị file upload

// Định nghĩa các level quyền hạn cho thành viên dự án
define('ROLE_OBSERVER', 1);    // Level 1: Chỉ xem
define('ROLE_CONTRIBUTOR', 2); // Level 2: Thêm/sửa ghi chú của mình
define('ROLE_MODERATOR', 3);   // Level 3: Sửa/xóa bất kỳ ghi chú
define('ROLE_OWNER', 4);       // Level 4: Toàn quyền (quản lý thành viên, thay đổi trạng thái)

// Các trạng thái cho ghi chú
$STATUSES = [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'resolved' => 'Đã giải quyết'
];
?>