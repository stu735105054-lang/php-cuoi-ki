<?php
require_once 'db.php';
require_once 'auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (login($email, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Email hoặc mật khẩu sai!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css_view.css">
</head>
<body>
    <div class="container">
        <div class="login">
            <h1>Smart Notes - Đăng nhập</h1>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <button type="submit">Đăng nhập</button>
            </form>
            <?php if ($error != '') { echo "<p class='error'>$error</p>"; } ?>
            <a href="register.php">Chưa có tài khoản? Đăng ký</a>
        </div>
    </div>
</body>
</html>