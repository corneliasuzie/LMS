<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? 0;
    $course_id = $data['course_id'] ?? 0;
    if (!$user_id || !$course_id) { echo json_encode(['success'=>false,'message'=>'user_id and course_id required']); exit; }
    $stmt = mysqli_prepare($conn, "SELECT id FROM enrollments WHERE user_id=? AND course_id=?");
    mysqli_stmt_bind_param($stmt,'ii',$user_id,$course_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($res)) { echo json_encode(['success'=>false,'message'=>'Already enrolled']); exit; }
    $stmt2 = mysqli_prepare($conn, "INSERT INTO enrollments (user_id,course_id) VALUES (?,?)");
    mysqli_stmt_bind_param($stmt2,'ii',$user_id,$course_id);
    if (mysqli_stmt_execute($stmt2)) echo json_encode(['success'=>true,'enroll_id'=>mysqli_insert_id($conn)]);
    else echo json_encode(['success'=>false,'error'=>mysqli_error($conn)]);
    exit;
}

if ($method === 'GET') {
    // return enrollments/progress for user
    $user = intval($_GET['user_id'] ?? 0);
    if (!$user) { echo json_encode(['success'=>false,'message'=>'user_id required']); exit; }
    $q = mysqli_query($conn, "SELECT e.id,e.course_id,c.title,p.progress_pct FROM enrollments e LEFT JOIN courses c ON c.id=e.course_id LEFT JOIN progress p ON p.user_id=e.user_id AND p.course_id=e.course_id WHERE e.user_id=".intval($user));
    $rows=[]; while($r=mysqli_fetch_assoc($q)) $rows[]=$r;
    echo json_encode(['success'=>true,'enrollments'=>$rows]);
    exit;
}

http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']);

?>
