<?php
require_once 'auth.php';
require_once 'db.php';
header('Content-Type: application/json');

$note_id = (int)$_POST['note_id'];  // Thay vì GET, dùng POST từ form
$stmt = $pdo->prepare("SELECT project_id, title FROM notes WHERE id = ?");
$stmt->execute([$note_id]);
$note = $stmt->fetch();
if (!$note) {
    echo json_encode(['success' => false]);
    exit;
}

$email = trim($_POST['email'] ?? '');
if ($email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        addNotification($user['id'], getUserName() . " đã chia sẻ ghi chú: " . $note['title'] . " với bạn");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
header("Location: note.php?id=" . $note_id);  // Redirect về note
exit;
?>