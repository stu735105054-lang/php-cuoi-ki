<?php
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'User';
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function getUserRole($project_id, $user_id = null) {
    global $pdo;
    if ($user_id == null) $user_id = getUserId();
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->fetchColumn() ?: 0;
}

function can($project_id, $action) {
    $role = getUserRole($project_id);
    if ($role == ROLE_OWNER) return true;
    if ($action == 'view' && $role >= ROLE_OBSERVER) return true;
    if ($action == 'add_note' && $role >= ROLE_CONTRIBUTOR) return true;
    if ($action == 'edit_note' && $role >= ROLE_CONTRIBUTOR) return true;
    if ($action == 'delete_note' && $role >= ROLE_MODERATOR) return true;
    if ($action == 'change_status' && $role == ROLE_OWNER) return true;
    if ($action == 'manage_members' && $role == ROLE_OWNER) return true;
    return false;
}

function addNotification($user_id, $msg) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user_id, $msg]);
}

function register($email, $password, $name) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return false;
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
    $stmt->execute([$email, $hashed_pass, $name]);
    return true;
}

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