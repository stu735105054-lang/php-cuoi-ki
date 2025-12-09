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
        
        <div class="project-header" style="margin-bottom: 30px; padding: 25px; background: #fff; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
            <h2 style="margin-bottom: 15px; color: #2c3e50;">Tạo Dự án Mới</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Tên dự án" style="width:100%;padding:12px;margin:10px 0; border: 1px solid #ddd; border-radius: 6px;"> 
                <textarea name="desc" placeholder="Mô tả dự án" style="width:100%;padding:12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; height: 100px;"></textarea>
                <input type="file" name="project_file" style="margin:10px 0; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                <button name="create" type="submit" style="padding: 12px 20px; background: #28a745; border: none; color: white; border-radius: 6px; cursor: pointer;">Tạo Dự án</button>
            </form>
        </div>
        
        <h2 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">Dự án của bạn</h2>
        
        <?php foreach ($projects as $p): ?>
            <div class="note" style="margin-bottom: 20px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.08);">
                <h3 style="color: #2c3e50; margin-bottom: 10px;"><?php echo htmlspecialchars($p['title']); ?> <?php if ($p['role'] == 4) echo "<span style='color: #28a745;'>(Bạn là chủ)</span>"; ?></h3>
                
                <p style="margin-bottom: 15px; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($p['description'] ? $p['description'] : 'Chưa có mô tả')); ?></p>
                
                <?php if ($p['file_path']): 
                    $ext = strtolower(pathinfo($p['file_path'], PATHINFO_EXTENSION));
                    $img_ext = ['jpg','jpeg','png','gif','webp','bmp'];
                ?>
                    <?php if (in_array($ext, $img_ext)): ?>
                        <img src="<?php echo UPLOAD_URL . str_replace('uploads/', '', $p['file_path']); ?>" alt="Preview" class="preview-img">
                    <?php endif; ?>
                <?php endif; ?>
                
                <div style="margin-top: 15px;">
                    <a href="project.php?id=<?php echo $p['id']; ?>" class="btn" style="background: #007bff; padding: 10px 20px;">Vào dự án</a>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
</div>
</body>
</html>