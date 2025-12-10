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
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .register { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .register h1 { color: #2c3e50; margin-bottom: 30px; font-size: 24px; }
        .register input { width: 100%; padding: 14px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        .register button { width: 100%; padding: 14px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin-top: 15px; }
        .register button:hover { background: #218838; }
        .error { color: #e74c3c; margin: 10px 0; padding: 10px; background: #ffeaea; border-radius: 6px; }
        .register a { display: block; margin-top: 20px; color: #1EA7FF; text-decoration: none; }
        .register a:hover { text-decoration: underline; }
    </style>
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
            <?php if ($error) echo "<p class='error'>$error</p>"; ?>
            <a href="Trangdangnhap.php">Đã có tài khoản? Đăng nhập</a>
        </div>
    </div>
</body>
</html>