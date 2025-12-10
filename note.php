<?php
require_once 'auth.php';
require_once 'db.php';
if (!isLoggedIn()) header('Location: Trangdangnhap.php');

$note_id = (int)($_GET['id'] ?? 0);
if (!$note_id) die('Không tìm thấy ghi chú');

$stmt = $pdo->prepare("SELECT n.*, p.title as project_title, p.id as project_id, u.name as author_name 
                     FROM notes n 
                     JOIN projects p ON n.project_id = p.id 
                     JOIN users u ON n.author_id = u.id 
                     WHERE n.id = ?");
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if (!$note) die('Ghi chú không tồn tại');

$role = getUserRole($note['project_id']);
$canEdit = can($note['project_id'], 'edit_note') && ($note['author_id'] == getUserId() || $role >= ROLE_MODERATOR);
$canChangeStatus = $role == ROLE_OWNER;

// Xử lý upload file cho note nếu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['note_file']) && $canEdit) {
    if ($_FILES['note_file']['error'] == 0) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $file = $_FILES['note_file'];
        $name = time() . "_" . basename($file['name']);
        $target = UPLOAD_DIR . $name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("UPDATE notes SET file_path = ? WHERE id = ?");
            $stmt->execute(["uploads/$name", $note_id]);
            addNotification(getUserId(), "Đã upload file cho ghi chú: " . $note['title']);
            header("Location: note.php?id=$note_id");  // Reload trang
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($note['title']) ?> - Smart Notes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .note-detail-container { max-width: 900px; margin: 0 auto; padding: 30px; }
        .note-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .note-title-edit { font-size: 32px; font-weight: bold; border: none; background: transparent; width: 100%; }
        .note-title-edit:focus { outline: 2px solid #1EA7FF; border-radius: 8px; }
        .note-meta { color: #666; font-size: 14px; margin-bottom: 20px; }
        .note-content { min-height: 400px; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-size: 17px; line-height: 1.8; }
        .status-selector { padding: 10px 16px; font-size: 16px; border-radius: 8px; border: 1px solid #ddd; }
        .action-bar { margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap; }
        .back-btn { background: #666; color:white; padding:10px 20px; border-radius:6px; text-decoration:none; }
        .project-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #1EA7FF; }
        .preview-img { max-width: 100%; max-height: 400px; border-radius: 12px; margin: 15px 0; box-shadow: 0 8px 25px rgba(0,0,0,0.15); display: block; }
    </style>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sb-top"><div class="sb-title">Smart Notes</div></div>
        <div class="notifications">
            <strong>Thông báo</strong>
            <div class="notif-item">Bạn đang xem ghi chú: <?php echo htmlspecialchars($note['title']); ?></div>
        </div>
        <div class="sidebar-menu">
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="btn">Trang Quản Trị (Admin)</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
        </div>
    </aside>
    <main class="main">
        <div class="note-detail-container">
            <!-- Info dự án -->
            <div class="project-info">
                <strong>Dự án:</strong> <?php echo htmlspecialchars($note['project_title']); ?>
            </div>
            
            <div class="note-header">
                <input type="text" class="note-title-edit" value="<?php echo htmlspecialchars($note['title']); ?>" onblur="updateTitle(<?php echo $note_id; ?>, this.value)" <?php if (!$canEdit) echo 'readonly'; ?>>
            </div>
            <div class="note-meta">
                Tác giả: <?php echo htmlspecialchars($note['author_name']); ?> | Cập nhật: <?php echo $note['updated_at']; ?> | Trạng thái: 
                <?php if ($canChangeStatus): ?>
                    <select class="status-selector" onchange="updateStatus(<?php echo $note_id; ?>, this.value)">
                        <option value="pending" <?php if($note['status']=='pending') echo 'selected'; ?>>Chờ xử lý</option>
                        <option value="confirmed" <?php if($note['status']=='confirmed') echo 'selected'; ?>>Đã xác nhận</option>
                        <option value="processing" <?php if($note['status']=='processing') echo 'selected'; ?>>Đang xử lý</option>
                        <option value="resolved" <?php if($note['status']=='resolved') echo 'selected'; ?>>Đã giải quyết</option>
                    </select>
                <?php else: ?>
                    <?php echo $STATUSES[$note['status']]; ?>
                <?php endif; ?>
            </div>
            <div class="note-content">
                <?php echo nl2br(htmlspecialchars($note['content'])) ?>
            </div>
            <?php if ($note['file_path']): 
                $ext = strtolower(pathinfo($note['file_path'], PATHINFO_EXTENSION));
                $img_ext = ['jpg','jpeg','png','gif','webp','bmp'];
            ?>
                <?php if (in_array($ext, $img_ext)): ?>
                    <img src="<?php echo UPLOAD_URL . basename($note['file_path']); ?>" alt="Preview" class="preview-img">
                <?php endif; ?>
            <?php endif; ?>
            <div class="action-bar">
                <?php if ($canEdit): ?>
                    <a href="edit_note.php?id=<?= $note_id ?>" class="btn" style="background:#1EA7FF; color:white; padding:10px 20px; text-decoration:none;">✏️ Chỉnh sửa nội dung</a>
                    <button onclick="shareNote(<?= $note_id ?>)" style="background:#6f42c1; color:white; padding:10px 20px; border:none; cursor:pointer;">📤 Chia sẻ ghi chú</button>
                    <form method="post" enctype="multipart/form-data" style="display:inline;">
                        <input type="file" name="note_file" onchange="this.form.submit()" style="display:none;" id="file-upload">
                        <label for="file-upload" style="background:#ffc107; color:white; padding:10px 20px; cursor:pointer; border-radius:6px;">📎 Upload file</label>
                        <input type="hidden" name="note_id" value="<?= $note_id ?>">
                    </form>
                <?php endif; ?>
            </div>

            <div style="margin-top:40px;">
                <a href="project.php?id=<?= $note['project_id'] ?>" class="back-btn">← Quay lại dự án</a>
            </div>
        </div>
    </main>
</div>

<script>
// Cập nhật title AJAX (giả sử có file update_note_ajax.php, nhưng thầy chưa thêm, em có thể tạo nếu cần)
function updateTitle(id, title) {
    fetch('update_note_ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&title=' + encodeURIComponent(title)
    });
}

// Cập nhật status AJAX (sử dụng update_status.php)
function updateStatus(id, status) {
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'note_id=' + id + '&status=' + status
    }).then(() => location.reload());
}

// Chia sẻ note
function shareNote(id) {
    let email = prompt("Nhập email người nhận:");
    if (email) {
        fetch('share_note.php', {
            method: 'POST',
            body: new URLSearchParams({note_id: id, email: email})
        }).then(r => r.json()).then(d => alert(d.success ? "Đã gửi!" : "Lỗi"));
    }
}
</script>
</body>
</html>