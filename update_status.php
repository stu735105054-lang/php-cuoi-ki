<?php
require_once 'auth.php';
require_once 'db.php';

$note_id = (int)$_POST['note_id'];
$status = $_POST['status'];

$stmt = $pdo->prepare("SELECT project_id FROM notes WHERE id = ?");
$stmt->execute([$note_id]);
$note = $stmt->fetch();
if (!$note || !can($note['project_id'], 'change_status')) {
    header("Location: project.php?id=" . $note['project_id']);
    exit;
}

$stmt = $pdo->prepare("UPDATE notes SET status = ? WHERE id = ?");
$stmt->execute([$status, $note_id]);
addNotification(getUserId(), "Đã thay đổi trạng thái ghi chú thành: " . $STATUSES[$status]);

header("Location: project.php?id=" . $note['project_id']);
exit;
?>