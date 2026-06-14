<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$name = trim($data['name'] ?? $data['nom'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'name, email and password are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid email']);
    exit;
}

// check duplicate
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_fetch_assoc($res)) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'Email already registered']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'student';
$stmt2 = mysqli_prepare($conn, "INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
mysqli_stmt_bind_param($stmt2, 'ssss', $name, $email, $hash, $role);
if (!mysqli_stmt_execute($stmt2)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Database error','error'=>mysqli_error($conn)]);
    exit;
}

$user_id = mysqli_insert_id($conn);
if (session_status() == PHP_SESSION_NONE) session_start();
$_SESSION['user'] = ['id'=>$user_id,'name'=>$name,'email'=>$email,'role'=>$role];

echo json_encode(['success'=>true,'user'=>$_SESSION['user']]);
exit;

?>
<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Champs requis manquants']);
    exit;
}

// vérifier si utilisateur existe
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? LIMIT 1");
mysqli_stmt_bind_param($stmt,'s',$email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_fetch_assoc($res)) {
    echo json_encode(['success'=>false,'message'=>'Email déjà utilisé']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt2 = mysqli_prepare($conn, "INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
$role = 'student';
mysqli_stmt_bind_param($stmt2,'ssss',$name,$email,$hash,$role);
if (mysqli_stmt_execute($stmt2)) {
    echo json_encode(['success'=>true,'id'=>mysqli_insert_id($conn)]);
} else {
    echo json_encode(['success'=>false,'error'=>mysqli_error($conn)]);
}
?>
