<?php
require_once 'db.php';  // Cần PDO để query

// Kiểm tra user đã login chưa (dựa vào session)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Lấy user_id từ session
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Lấy tên user từ session
function getUserName() {
    return $_SESSION['user_name'] ?? 'User';
}

// Kiểm tra user có phải admin không
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Lấy role của user trong dự án cụ thể
function getUserRole($project_id, $user_id = null) {
    global $pdo;
    if ($user_id == null) $user_id = getUserId();
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->fetchColumn() ?: 0;  // Trả về 0 nếu không phải thành viên
}

// Kiểm tra quyền hạn trong dự án
function can($project_id, $action) {
    $role = getUserRole($project_id);
    if ($role == ROLE_OWNER) return true;  // Owner làm được hết
    switch ($action) {
        case 'view': return $role >= ROLE_OBSERVER;
        case 'add_note': return $role >= ROLE_CONTRIBUTOR;
        case 'edit_note': return $role >= ROLE_CONTRIBUTOR;
        case 'delete_note': return $role >= ROLE_MODERATOR;
        case 'change_status': return $role == ROLE_OWNER;
        case 'manage_members': return $role == ROLE_OWNER;
        default: return false;
    }
}

// Thêm thông báo cho user
function addNotification($user_id, $msg) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user_id, $msg]);
}

// Đăng ký user mới
function register($email, $password, $name) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return false;  // Email đã tồn tại
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);  // Hash mật khẩu
    $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
    $stmt->execute([$email, $hashed_pass, $name]);
    return true;
}

// Đăng nhập
function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        return true;
    }
    return false;
}
?>