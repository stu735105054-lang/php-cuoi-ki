<?php
require_once 'auth.php';
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: Trangdangnhap.php');
    exit;
}

$note_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if (!$note) {
    header("Location: dashboard.php");
    exit;
}

if (!can($note['project_id'], 'edit_note')) {
    header("Location: project.php?id=" . $note['project_id']);
    exit;
}

// Lấy thông tin project và owner
$stmt_project = $pdo->prepare("SELECT p.*, u.name as creator_name FROM projects p JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
$stmt_project->execute([$note['project_id']]);
$project = $stmt_project->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if ($title && $content) {
        $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $note_id]);
        addNotification(getUserId(), "Bạn đã sửa ghi chú: $title");
    }
    header("Location: project.php?id=" . $note['project_id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa ghi chú</title>
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .register { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 500px; text-align: center; }
        .project-info { margin-bottom: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #1EA7FF; }
        .project-info p { margin: 5px 0; }
        .form-input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; }
        .form-textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; height: 300px; }
        .btn-save { padding: 10px 20px; background: #28a745; border: none; color: white; border-radius: 6px; cursor: pointer; }
        .btn-cancel { display: inline-block; margin-top: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; }
        .btn-save:hover { background: #218838; }
        .btn-cancel:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container"> 
        <div class="register"> 
            <h1>Sửa ghi chú: <?php echo htmlspecialchars($note['title']); ?></h1> 
            
            <!-- Hiển thị info project -->
            <div class="project-info">
                <p><strong>Project:</strong> <?php echo htmlspecialchars($project['title']); ?></p>
                <p><strong>Người tạo:</strong> <?php echo htmlspecialchars($project['creator_name']); ?></p>
            </div>
            
            <form method="post">
                <input type="text" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" placeholder="Tiêu đề" class="form-input" required> 
                <textarea name="content" placeholder="Nội dung" class="form-textarea" required><?php echo htmlspecialchars($note['content']); ?></textarea> 
                <button type="submit" class="btn-save">Lưu thay đổi</button> 
            </form>
            <a href="project.php?id=<?php echo $note['project_id']; ?>" class="btn-cancel">Hủy</a>
        </div>
    </div>
</body>
</html>