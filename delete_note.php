<?php
require_once 'auth.php';
require_once 'db.php';
if (!isLoggedIn()) {
    header('Location: Trangdangnhap.php');
    exit;
}

$note_id = (int)$_GET['id'];
$pid = (int)$_GET['project_id'];

$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ? AND project_id = ?");
$stmt->execute([$note_id, $pid]);
$note = $stmt->fetch();
if (!$note || !can($pid, 'delete_note')) {
    header("Location: project.php?id=$pid");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
$stmt->execute([$note_id]);
addNotification(getUserId(), "Bạn đã xóa ghi chú");

header("Location: project.php?id=$pid");
exit;
?>