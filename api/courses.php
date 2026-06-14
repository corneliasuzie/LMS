<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $q = mysqli_query($conn, "SELECT id,title,description FROM courses ORDER BY id DESC");
    $rows = [];
    while ($r = mysqli_fetch_assoc($q)) $rows[] = $r;
    echo json_encode(['success'=>true,'courses'=>$rows]);
    exit;
}

if ($method === 'POST') {
    // create course (simple, no auth robust check)
    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'] ?? '';
    $desc = $data['description'] ?? '';
    $stmt = mysqli_prepare($conn, "INSERT INTO courses (title,description) VALUES (?,?)");
    mysqli_stmt_bind_param($stmt,'ss',$title,$desc);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success'=>true,'id'=>mysqli_insert_id($conn)]);
    } else echo json_encode(['success'=>false,'error'=>mysqli_error($conn)]);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);

?>
