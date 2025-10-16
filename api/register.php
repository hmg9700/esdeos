<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
header('Content-Type: application/json');

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid CSRF']); exit; }

$username = trim((string)($_POST['username'] ?? ''));
$full_name = trim((string)($_POST['full_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$pwd = (string)($_POST['password'] ?? '');

if (!$username || !$full_name || !$email || strlen($pwd) < 6) {
  echo json_encode(['ok'=>false,'error'=>'Invalid inputs']); exit;
}

try {
  $hash = password_hash($pwd, PASSWORD_BCRYPT);
  $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password_hash, level, status) VALUES (?, ?, ?, ?, 'user', 'deactive')");
  $stmt->execute([$username, $full_name, $email, $hash]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Email or username already exists']);
}
