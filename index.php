<?php
require_once 'auth.php';  // Load auth functions

if (isLoggedIn()) {
    header('Location: dashboard.php');  // Nếu login rồi, đi dashboard
} else {
    header('Location: Trangdangnhap.php');  // Chưa login, đi trang đăng nhập
}
exit;
?>