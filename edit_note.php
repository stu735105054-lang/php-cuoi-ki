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

<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa ghi chú</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="css_view.css"> 
</head>
<body>
<div class="container"> 
<div class="register"> 
<h1>Sửa ghi chú: <?php echo htmlspecialchars($note['title']); ?></h1> 
<form method="post">
<input type="text" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" placeholder="Tiêu đề" required> 
<textarea name="content" placeholder="Nội dung" required style="height:300px;"><?php echo htmlspecialchars($note['content']); ?></textarea> 
<button type="submit">Lưu thay đổi</button> 
</form>
<a href="project.php?id=<?php echo $note['project_id']; ?>">Hủy</a>
</div>
</div>
</body>
</html>