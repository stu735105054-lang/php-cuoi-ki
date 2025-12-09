<?php
session_start();
session_destroy();
header('Location: Trangdangnhap.php');
exit;
?>