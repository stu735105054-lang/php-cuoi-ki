<?php
require_once 'auth.php';
require_once 'db.php';
if (!isLoggedIn()) {
    header('Location: Trangdangnhap.php');
    exit;
}

$pid = (int)$_POST['project_id'];
if (!can($pid, 'add_note')) {
    header("Location: project.php?id=$pid");
    exit;
}

$title = trim($_POST['title']);
$content = trim($_POST['content']);

if ($title && $content) {
    $stmt = $pdo->prepare("INSERT INTO notes (project_id, title, content, author_id, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$pid, $title, $content, getUserId()]);
    $note_id = $pdo->lastInsertId();
    addNotification(getUserId(), "Bạn đã thêm ghi chú mới: $title");
    
    $stmt = $pdo->prepare("SELECT owner_id FROM projects WHERE id = ?");
    $stmt->execute([$pid]);
    $owner = $stmt->fetchColumn();
    if ($owner != getUserId()) {
        addNotification($owner, getUserName() . " đã thêm ghi chú mới trong dự án của bạn");
    }
    
    if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] == 0) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $file = $_FILES['note_file'];
        $name = time() . "_" . basename($file['name']);
        $target = UPLOAD_DIR . $name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("UPDATE notes SET file_path = ? WHERE id = ?");
            $stmt->execute(["uploads/$name", $note_id]);
            addNotification(getUserId(), "Đã upload file cho ghi chú mới");
        }
    }
}

header("Location: project.php?id=$pid");
exit;
?>