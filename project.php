<?php
require_once 'auth.php';
if (!isLoggedIn()) {
    header('Location: Trangdangnhap.php');
    exit;
}
require_once 'db.php';

$pid = (int)($_GET['id'] ?? 0);
$role = getUserRole($pid);
if ($role == 0) {
    die("Bạn không có quyền truy cập dự án này!");
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$pid]);
$project = $stmt->fetch();
if (!$project) {
    die("Dự án không tồn tại!");
}

if (isset($_POST['edit_project']) && can($pid, 'manage_members')) {
    $title = $_POST['title'];
    $desc = $_POST['desc'];
    $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$title, $desc, $pid]);
    addNotification(getUserId(), "Bạn đã sửa thông tin dự án: $title");
    header("Location: project.php?id=$pid");
    exit;
}

$stmt = $pdo->prepare("SELECT n.*, u.name as author FROM notes n JOIN users u ON n.author_id = u.id WHERE n.project_id = ? ORDER BY n.updated_at DESC");
$stmt->execute([$pid]);
$notes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($project['title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sb-top"><div class="sb-title">Smart Notes</div></div>
        <div class="notifications">
            <strong>Thông báo</strong>
            <div class="notif-item">Bạn đang xem dự án: <?php echo htmlspecialchars($project['title']); ?></div>
            <div class="notif-item">
                <?php
                $row = $pdo->prepare("SELECT name FROM users WHERE id = (SELECT owner_id FROM projects WHERE id = ?)");
                $row->execute([$pid]);
                $owner_name = $row->fetchColumn();
                echo "<span style='color: #144ae0ff;'>(Tài khoản: " . htmlspecialchars($owner_name) . ")</span>";
                ?>
            </div>
        </div>
        <div class="sidebar-menu">
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="btn">Trang Quản Trị (Admin)</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
        </div>
    </aside>
    <main class="main">
        <div class="project-header">
            <h2><?php echo htmlspecialchars($project['title']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
            <?php if (can($pid, 'manage_members')) { ?>
                <form method="post">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
                    <textarea name="desc"><?php echo htmlspecialchars($project['description']); ?></textarea>
                    <button type="submit" name="edit_project" style="padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Cập nhật</button>
                </form>
            <?php } ?>
            
            <?php if ($project['file_path']): 
                $ext = strtolower(pathinfo($project['file_path'], PATHINFO_EXTENSION));
                $img_ext = ['jpg','jpeg','png','gif','webp','bmp'];
            ?>
                <?php if (in_array($ext, $img_ext)): ?>
                    <img src="<?php echo UPLOAD_URL . basename($project['file_path']); ?>" alt="Preview" class="preview-img">
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <?php if (can($pid, 'add_note')) { ?>
            <div class="note" style="background:#e2e3e5; padding:20px; border-radius:10px; margin-bottom:30px;">
                <h3>Thêm Ghi Chú Mới</h3>
                <form method="post" action="add_note.php" enctype="multipart/form-data">
                    <input type="hidden" name="project_id" value="<?php echo $pid; ?>">
                    <input type="text" name="title" placeholder="Tiêu đề" required style="width:100%; padding:10px; margin-bottom:10px;">
                    <textarea name="content" placeholder="Nội dung" required style="width:100%; height:150px; padding:10px; margin-bottom:10px;"></textarea>
                    <input type="file" name="note_file" style="margin-bottom:10px;">
                    <button type="submit" style="padding:10px 20px; background:#28a745; color:white; border:none; cursor:pointer;">Thêm</button>
                </form>
            </div>
        <?php } ?>
        
        <?php foreach ($notes as $note): ?>
            <div class="note" style="margin-bottom:20px; padding:20px; background:white; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                <p style="color:#666; font-size:14px;">Tác giả: <?php echo htmlspecialchars($note['author']); ?> | Cập nhật: <?php echo $note['updated_at']; ?></p>
                <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                
                <?php if ($note['file_path']): 
                    $ext = strtolower(pathinfo($note['file_path'], PATHINFO_EXTENSION));
                    $img_ext = ['jpg','jpeg','png','gif','webp','bmp'];
                ?>
                    <?php if (in_array($ext, $img_ext)): ?>
                        <img src="<?php echo UPLOAD_URL . basename($note['file_path']); ?>" alt="Preview" class="preview-img">
                    <?php endif; ?>
                <?php endif; ?>
                
                <div style="margin-top:15px; display:flex; gap:10px;">
                    <span style="padding:8px 15px; background:#ffc107; color:white; border-radius:4px;"><?php echo $STATUSES[$note['status']]; ?></span>
                    
                    <?php if (can($pid, 'change_status')): ?>
                        <form method="post" action="update_status.php" style="display:inline;">
                            <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?php if($note['status']=='pending') echo 'selected'; ?>>Chờ xử lý</option>
                                <option value="confirmed" <?php if($note['status']=='confirmed') echo 'selected'; ?>>Đã xác nhận</option>
                                <option value="processing" <?php if($note['status']=='processing') echo 'selected'; ?>>Đang xử lý</option>
                                <option value="resolved" <?php if($note['status']=='resolved') echo 'selected'; ?>>Đã giải quyết</option>
                            </select>
                        </form>
                    <?php endif; ?>
                    
                    <?php $canEdit = can($pid, 'edit_note') && ($note['author_id'] == getUserId() || $role >= ROLE_MODERATOR); ?>
                    <?php if ($canEdit) { ?>
                        <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="btn btn-small" style="background: #28a745; padding: 8px 15px;">Sửa</a>
                    <?php } ?>
                    
                    <?php $canDelete = can($pid, 'delete_note') && ($note['author_id'] == getUserId() || $role >= ROLE_MODERATOR); ?>
                    <?php if ($canDelete) { ?>
                        <a href="delete_note.php?id=<?php echo $note['id']; ?>&project_id=<?php echo $pid; ?>" class="btn btn-small btn-danger" onclick="return confirm('Xóa ghi chú này?')" style="padding: 8px 15px;">Xóa</a>
                    <?php } ?>
                    
                    <a href="share_note.php?id=<?php echo $note['id']; ?>" class="btn btn-small" style="background: #6f42c1; padding: 8px 15px;">Chia sẻ</a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (can($pid, 'manage_members')) { ?>
            <div class="note" style="background:#fff3cd; padding:20px; border-radius:10px; margin-bottom:30px;">
                <h3>Mời thành viên mới</h3>
                <form method="post" action="invite_member.php">
                    <input type="hidden" name="project_id" value="<?php echo $pid; ?>">
                    <input type="email" name="email" placeholder="Email người mời" required style="width:100%; padding:10px; margin-bottom:10px;">
                    <select name="role" style="width:100%; padding:10px; margin-bottom:10px;">
                        <option value="1">Observer (Level 1)</option>
                        <option value="2">Contributor (Level 2)</option>
                        <option value="3">Moderator (Level 3)</option>
                    </select>
                    <button type="submit" style="padding:10px 20px; background:#007bff; color:white; border:none; cursor:pointer;">Mời tham gia</button>
                </form>
            </div>
        <?php } ?>
        
        <a href="dashboard.php" class="btn" style="margin-top:20px; padding:10px 20px; background:#6c757d; color:white;">Quay về Dashboard</a>
    </main>
</div>
</body>
</html>