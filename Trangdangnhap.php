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
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .login input {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .login button {
            width: 100%;
            padding: 14px;
            background: #1EA7FF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        .login button:hover {
            background: #0d8bff;
        }
        .error {
            color: #e74c3c;
            margin: 10px 0;
            padding: 10px;
            background: #ffeaea;
            border-radius: 6px;
        }
        .login a {
            display: block;
            margin-top: 20px;
            color: #1EA7FF;
            text-decoration: none;
        }
        .login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login">
            <h1>Smart Notes - Đăng nhập</h1>
            <form method="POST">
                <input type="email" name="email" placeholder="Email">
                <input type="password" name="password" placeholder="Mật khẩu">
                <button type="submit">Đăng nhập</button>
            </form>
            <?php if ($error != '') { echo "<p class='error'>$error</p>"; } ?>
            <a href="register.php">Chưa có tài khoản? Đăng ký</a>
        </div>
    </div>
</body>
</html>