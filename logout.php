<?php
session_start();
session_destroy();  // Xóa hết session
header('Location: Trangdangnhap.php');  // Redirect về login
exit;
?>