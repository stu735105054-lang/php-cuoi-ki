<?php
require_once 'auth.php';
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}
require_once 'db.php';

if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    if ($id != getUserId()) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
}

if (isset($_GET['delete_note'])) {
    $id = (int)$_GET['delete_note'];
    $pdo->prepare("DELETE FROM notes WHERE id = ?")->execute([$id]);
}

if (isset($_GET['delete_project'])) {
    $id = (int)$_GET['delete_project'];
    $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
}

$users = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll();
$notes = $pdo->query("SELECT n.*, p.title as project, u.name as author FROM notes n JOIN projects p ON n.project_id = p.id JOIN users u ON n.author_id = u.id")->fetchAll();
$projects = $pdo->query("SELECT * FROM projects")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sb-top"><div class="sb-title">Smart Notes</div></div>
        <div class="notifications">
            <strong>Thông báo</strong>
            <div class="notif-item">Bạn đang ở trang Admin</div>
        </div>
        <div class="sidebar-menu">
            <a href="admin.php" class="btn">Trang Quản Trị (Admin)</a> <!-- Luôn hiển thị vì đang ở admin -->
            <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
        </div>
    </aside>
    <main class="main">
        <h1>Trang Quản Trị Hệ Thống</h1>

        <h2>Quản lý Người dùng</h2>
        <table>
            <tr><th>ID</th><th>Tên</th><th>Email</th><th>Hành động</th></tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <?php if ($u['id'] != getUserId()): ?>
                            <a href="admin.php?delete_user=<?php echo $u['id']; ?>" onclick="return confirm('Xóa user này?')" class="btn btn-danger">Xóa</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Quản lý Dự án</h2>
        <table>
            <tr><th>ID</th><th>Tiêu đề</th><th>Owner</th><th>Hành động</th></tr>
            <?php foreach ($projects as $p): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                    <td><?php echo $p['owner_id']; ?></td>
                    <td><a href="admin.php?delete_project=<?php echo $p['id']; ?>" onclick="return confirm('Xóa dự án này?')" class="btn btn-danger">Xóa</a></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Quản lý Ghi chú</h2>
        <table>
            <tr><th>ID</th><th>Tiêu đề</th><th>Dự án</th><th>Tác giả</th><th>Hành động</th></tr>
            <?php foreach ($notes as $n): ?>
                <tr>
                    <td><?php echo $n['id']; ?></td>
                    <td><?php echo htmlspecialchars($n['title']); ?></td>
                    <td><?php echo htmlspecialchars($n['project']); ?></td>
                    <td><?php echo htmlspecialchars($n['author']); ?></td>
                    <td><a href="admin.php?delete_note=<?php echo $n['id']; ?>" onclick="return confirm('Xóa ghi chú này?')" class="btn btn-danger">Xóa</a></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="dashboard.php" class="btn">Quay về Dashboard</a>
    </main>
</div>
</body>
</html>