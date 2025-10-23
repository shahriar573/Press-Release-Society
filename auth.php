<?php
include 'config.php';

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : null;

if ($action === 'login') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if ($user === $CONFIG['admin_user'] && password_verify($pass, $CONFIG['admin_pass_hash'])) {
        $_SESSION['is_admin'] = true;
        echo json_encode(['ok'=>true,'msg'=>'Logged in']);
    } else {
        echo json_encode(['ok'=>false,'msg'=>'Invalid credentials']);
    }
    exit;
}

if ($action === 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(['ok'=>true,'msg'=>'Logged out']);
    exit;
}

// check
echo json_encode(['is_admin'=>isset($_SESSION['is_admin']) && $_SESSION['is_admin']]);
