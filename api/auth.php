<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $stmt = mysqli_prepare($conn, "SELECT id,name,email,password,role FROM users WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    if ($user) {
        $stored = $user['password'];
        $ok = false;
        if (strpos($stored, '$2y$') === 0 || strpos($stored, '$argon2') === 0) {
            $ok = password_verify($password, $stored);
        } else {
            $ok = ($password === $stored);
        }
        if ($ok) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
            echo json_encode(['success'=>true,'user'=>$_SESSION['user']]);
            exit;
        }
    }
    echo json_encode(['success'=>false,'message'=>'Identifiants invalides']);
    exit;
}

if ($action === 'logout') {
    if (session_status() == PHP_SESSION_NONE) session_start();
    session_destroy();
    echo json_encode(['success'=>true]);
    exit;
}

// whoami
if ($action === 'me') {
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user'])) {
        echo json_encode(['logged'=>true,'user'=>$_SESSION['user']]);
    } else {
        echo json_encode(['logged'=>false]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success'=>false,'message'=>'Action inconnue']);

?>
