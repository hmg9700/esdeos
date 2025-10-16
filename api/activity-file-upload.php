<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
if (!verify_csrf($_POST['csrf'] ?? '')) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF']); exit; }

$activityId = (int)($_POST['activity_id'] ?? 0);
if ($activityId <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Bad activity_id']); exit; }

$desc = trim($_POST['description'] ?? '');
if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No file']); exit; }

$uploadDir = dirname(__DIR__) . '/uploads/activity';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

$name = $_FILES['file']['name'];
$tmp = $_FILES['file']['tmp_name'];
$size = (int)$_FILES['file']['size'];
$ext = pathinfo($name, PATHINFO_EXTENSION);
$safeBase = bin2hex(random_bytes(8));
$stored = $safeBase . ($ext ? ('.' . preg_replace('/[^a-zA-Z0-9]+/','', $ext)) : '');
$dest = $uploadDir . '/' . $stored;

if (!move_uploaded_file($tmp, $dest)) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Failed upload']); exit; }

$ins = $pdo->prepare("INSERT INTO activity_files(activity_id, description, filename, size) VALUES(?,?,?,?)");
$ins->execute([$activityId, $desc, $stored, $size]);
echo json_encode(['ok' => true]);
