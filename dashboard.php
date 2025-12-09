<?php
require_once 'auth.php';
if (!isLoggedIn()) {
    header('Location: Trangdangnhap.php');
    exit;
}
require_once 'db.php';

$user_id = getUserId();

if (isset($_POST['create'])) {
    $title = $_POST['title'];
    $desc = $_POST['desc'];
    $stmt = $pdo->prepare("INSERT INTO projects (title, description, owner_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $desc, $user_id]);
    $pid = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 4)");
    $stmt->execute([$pid, $user_id]);
    addNotification($user_id, "Bạn đã tạo dự án mới: $title");
    
    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] == 0) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);  // Check và tạo folder nếu chưa có
        $file = $_FILES['project_file'];
        $name = time() . "_" . basename($file['name']);
        $target = UPLOAD_DIR . $name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("UPDATE projects SET file_path = ? WHERE id = ?");
            $stmt->execute(['uploads/' . $name, $pid]);
            addNotification($user_id, "Đã upload file cho dự án mới");
        }
    }
}

$stmt = $pdo->prepare("SELECT p.*, pm.role FROM projects p JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_id = ?");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$notifs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Smart Notes Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sb-top"><div class="sb-title">Smart Notes</div></div>
        <div class="search"><input type="text" placeholder="Tìm kiếm..."></div>
        <div class="divider"></div>
        <div class="notifications">
            <strong>Thông báo</strong>
            <?php foreach ($notifs as $n): ?>
                <div class="notif-item"><?php echo htmlspecialchars($n['message']); ?></div>
            <?php endforeach; ?>
        </div>
        <div class="sidebar-menu">
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="btn">Trang Quản Trị (Admin)</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
        </div>
    </aside>
    <main class="main">
        <h1>Xin chào, <?php echo getUserName(); ?>!</h1>
        <div class="project-header">
            <h2>Tạo Dự án Mới</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Tên dự án" required style="width:100%;padding:12px;margin:10px 0;">
                <textarea name="desc" placeholder="Mô tả dự án" style="width:100%;padding:12px;"></textarea>
                <input type="file" name="project_file" style="margin:10px 0;">
                <button name="create" type="submit">Tạo Dự án</button>
            </form>
        </div>
        <h2>Dự án của bạn</h2>
        <?php foreach ($projects as $p): ?>
            <div class="note">
                <h3><?php echo htmlspecialchars($p['title']); ?> <?php if ($p['role'] == 4) echo "(Bạn là chủ)"; ?></h3>
                <p><?php echo nl2br(htmlspecialchars($p['description'] ? $p['description'] : 'Chưa có mô tả')); ?></p>
                <?php if ($p['file_path']): 
                    $ext = strtolower(pathinfo($p['file_path'], PATHINFO_EXTENSION));
                    $img_ext = ['jpg','jpeg','png','gif','webp','bmp'];
                ?>
                    <?php if (in_array($ext, $img_ext)): ?>
                        <img src="<?php echo UPLOAD_URL . basename($p['file_path']); ?>" alt="Preview" class="preview-img">
                    <?php endif; ?>
                <?php endif; ?>
                <a href="project.php?id=<?php echo $p['id']; ?>" class="btn">Vào dự án</a>
            </div>
        <?php endforeach; ?>
    </main>
</div>
</body>
</html>