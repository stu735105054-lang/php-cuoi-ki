<?php
session_start();  // Bắt đầu session để lưu user_id
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/smartNotes/uploads/');
define('ROLE_OBSERVER', 1);  // Level 1: Chỉ xem
define('ROLE_CONTRIBUTOR', 2);  // Level 2: Thêm/edit ghi chú của mình
define('ROLE_MODERATOR', 3);  // Level 3: Edit/xóa bất kỳ ghi chú
define('ROLE_OWNER', 4);  // Level 4: Toàn quyền, thay đổi trạng thái, quản lý thành viên

$STATUSES = [  // Trạng thái ghi chú
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'resolved' => 'Đã giải quyết'
];
?>