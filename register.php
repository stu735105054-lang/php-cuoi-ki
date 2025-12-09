<?php
require_once 'db.php';
require_once 'auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    if (register($email, $password, $name)) {
        header('Location: Trangdangnhap.php');
        exit;
    } else {
        $error = 'Email đã tồn tại!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="css_view.css">  
</head>
<body>
    <div class="container">
        <div class="register">
            <h1>Smart Notes - Đăng ký</h1>
            <form method="POST">
                <input type="text" name="name" placeholder="Tên" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <button type="submit">Đăng ký</button>
            </form>
            <?php if ($error != '') { echo "<p class='error'>$error</p>"; } ?>
            <a href="Trangdangnhap.php">Đã có tài khoản? Đăng nhập</a>
        </div>
    </div>
</body>
</html>