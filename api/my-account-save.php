<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
header('Content-Type: application/json');
require_login_api();

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid CSRF']); exit; }

$uid = (int)($_SESSION['user']['id'] ?? 0);
$username = trim((string)($_POST['username'] ?? ''));
$full_name = trim((string)($_POST['full_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));

try {
  $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=? WHERE id=?");
  $stmt->execute([$username, $full_name, $email, $uid]);

  $_SESSION['user']['username'] = $username;
  $_SESSION['user']['full_name'] = $full_name;
  $_SESSION['user']['email'] = $email;

  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Email or username already exists']);
}
