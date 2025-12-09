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
            <p><?php echo nl2br(htmlspecialchars($project['description'] ? $project['description'] : 'Chưa có mô tả')); ?></p>
            <?php if ($project['file_path']) { ?>
                <?php 
                $ext = pathinfo($project['file_path'], PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), ['jpg', 'png', 'gif'])) { ?>
                    <img src="<?php echo $project['file_path']; ?>" alt="Preview" class="preview-img">
                <?php } ?>
            <?php } ?>
            <?php if (can($pid, 'manage_members')) { ?>
                <form method="post">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($project['title']); ?>">
                    <textarea name="desc"><?php echo htmlspecialchars($project['description']); ?></textarea>
                    <button name="edit_project" type="submit">Sửa dự án</button>
                </form>
            <?php } ?>
        </div>
        
        <?php if (can($pid, 'add_note')) { ?>
            <div class="project-header"> <!-- Dùng class tương tự cho form add note -->
                <h2>Thêm Ghi Chú Mới</h2>
                <form method="post" action="add_note.php" enctype="multipart/form-data">
                    <input type="hidden" name="project_id" value="<?php echo $pid; ?>">
                    <input type="text" name="title" placeholder="Tiêu đề ghi chú" style="width:100%;padding:12px;margin:10px 0;">
                    <textarea name="content" placeholder="Nội dung ghi chú" style="width:100%;padding:12px;height:150px;"></textarea>
                    <input type="file" name="note_file" style="margin:10px 0;">
                    <button type="submit">Thêm Ghi Chú</button>
                </form>
            </div>
        <?php } ?>
        
        <?php foreach ($notes as $note): ?>
            <div class="note" style="margin-bottom: 25px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.08);">
                <h3 style="color: #2c3e50; margin-bottom: 12px;"><?php echo htmlspecialchars($note['title']); ?></h3>
                
                <!-- Tác giả và thông tin -->
                <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; margin-bottom: 15px; border-left: 3px solid #1EA7FF;">
                    <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($note['author']); ?></p>
                    <p><strong>Cập nhật:</strong> <?php echo $note['updated_at']; ?></p>
                    <p><strong>Trạng thái:</strong> <?php echo $STATUSES[$note['status']]; ?></p>
                </div>
                
                <p style="line-height: 1.7; margin-bottom: 15px;"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                
                <?php if ($note['file_path']) { ?>
                    <?php 
                    $ext = pathinfo($note['file_path'], PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg', 'png', 'gif'])) { ?>
                        <img src="<?php echo $note['file_path']; ?>" alt="Preview" class="preview-img">
                    <?php } ?>
                <?php } ?>
                
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                    <?php if (can($pid, 'change_status')) { ?>
                        <form method="post" action="update_status.php" style="display: inline-block; margin-right: 10px;">
                            <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                            <select name="status" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
                                <?php foreach ($STATUSES as $k => $v): ?>
                                    <option value="<?php echo $k; ?>" <?php if ($note['status'] == $k) echo 'selected'; ?>><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" style="padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Cập nhật</button>
                        </form>
                    <?php } ?>
                    
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
            <div class="note" style="background:#fff3cd;">
                <h3>Mời thành viên mới</h3>
                <form method="post" action="invite_member.php">
                    <input type="hidden" name="project_id" value="<?php echo $pid; ?>">
                    <input type="email" name="email" placeholder="Email người mời">
                    <select name="role">
                        <option value="1">Observer (Level 1)</option>
                        <option value="2">Contributor (Level 2)</option>
                        <option value="3">Moderator (Level 3)</option>
                    </select>
                    <button type="submit">Mời tham gia</button>
                </form>
            </div>
        <?php } ?>
        
        <a href="dashboard.php" class="btn" style="margin-top:20px;">Quay về Dashboard</a>
    </main>
</div>
</body>
</html>