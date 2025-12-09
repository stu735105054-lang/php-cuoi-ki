<?php
require_once 'auth.php';
require_once 'db.php';
if (!isLoggedIn()) {
    header('Location: Trangdangnhap.php');
    exit;
}

$pid = (int)$_POST['project_id'];
$email = trim($_POST['email']);
$role = (int)$_POST['role'];

if (can($pid, 'manage_members')) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO project_members (project_id, user_id, role) VALUES (?, ?, ?)");
        $stmt->execute([$pid, $user['id'], $role]);
        addNotification($user['id'], "Bạn được mời tham gia dự án với vai trò level $role");
        addNotification(getUserId(), "Đã mời $email vào dự án");
    }
}
header("Location: project.php?id=$pid");
exit;
?>