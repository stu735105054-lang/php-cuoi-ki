<?php
require_once 'auth.php';
require_once 'db.php';
if (!isLoggedIn()) header('Location: index.php');

$note_id = (int)($_GET['id'] ?? 0);
if (!$note_id) die('Không tìm thấy ghi chú');

$note = $pdo->query("SELECT n.*, p.title as project_title, p.id as project_id, u.name as author_name 
                     FROM notes n 
                     JOIN projects p ON n.project_id = p.id 
                     JOIN users u ON n.author_id = u.id 
                     WHERE n.id = $note_id")->fetch();

if (!$note) die('Ghi chú không tồn tại');

$role = getUserRole($note['project_id']);
$canEdit = can($note['project_id'], 'edit_note') && ($note['author_id'] == getUserId() || $role >= ROLE_MODERATOR);
$canChangeStatus = $role == ROLE_OWNER;
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
        .back-btn { background: #666; }
    </style>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sb-top">
            <div class="sb-title">Smart Notes</div>
            <div class="sb-icons">
                <a href="project.php?id=<?= $note['project_id'] ?>" class="icon back">←</a>
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
        <div class="note-detail-container">
            <div class="note-header">
                <?php if ($canEdit): ?>
                    <input type="text" class="note-title-edit" value="<?= htmlspecialchars($note['title']) ?>" 
                           onblur="updateTitle(<?= $note_id ?>, this.value)">
                <?php else: ?>
                    <h2><?= htmlspecialchars($note['title']) ?></h2>
                <?php endif; ?>
                <?php if ($canChangeStatus): ?>
                    <select class="status-selector" onchange="updateStatus(<?= $note_id ?>, this.value)">
                        <?php foreach ($STATUSES as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $note['status'] == $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="note-meta">
                Tác giả: <?= htmlspecialchars($note['author_name']) ?> | Dự án: <?= htmlspecialchars($note['project_title']) ?> | Cập nhật: <?= $note['updated_at'] ?>
            </div>
            <div class="note-content">
                <?= nl2br(htmlspecialchars($note['content'])) ?>
            </div>
            <?php if ($note['file_path']): 
                $ext = strtolower(pathinfo($note['file_path'], PATHINFO_EXTENSION));
                $img_ext = ['jpg','jpeg','png','gif','webp','bmp'];
            ?>
                <?php if (in_array($ext, $img_ext)): ?>
                    <img src="<?= UPLOAD_URL . basename($note['file_path']) ?>" alt="Preview" class="preview-img">
                <?php endif; ?>
            <?php endif; ?>
            <div class="action-bar">
                <?php if ($canEdit): ?>
                    <a href="edit_note.php?id=<?= $note_id ?>" class="btn">✏️ Chỉnh sửa nội dung</a>
                    <button onclick="shareNote(<?= $note_id ?>)">📤 Chia sẻ ghi chú</button>
                    <form method="post" enctype="multipart/form-data" style="display:inline;">
                        <input type="file" name="note_file" onchange="this.form.submit()">
                        <input type="hidden" name="note_id" value="<?= $note_id ?>">
                        <button type="submit">📎 Upload file</button>
                    </form>
                <?php endif; ?>
            </div>

            <div style="margin-top:40px;">
                <a href="project.php?id=<?= $note['project_id'] ?>" class="btn back-btn">← Quay lại dự án</a>
            </div>
        </div>
    </main>
</div>

<script>
function updateTitle(id, title) {
    fetch('update_note_ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&title=' + encodeURIComponent(title)
    });
}

function updateStatus(id, status) {
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'note_id=' + id + '&status=' + status
    }).then(() => location.reload());
}

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