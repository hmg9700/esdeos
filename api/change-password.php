<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
header('Content-Type: application/json');
require_login_api();

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid CSRF']); exit; }

$uid = (int)($_SESSION['user']['id'] ?? 0);
$new = (string)($_POST['new_password'] ?? '');
if (strlen($new) < 6) { echo json_encode(['ok'=>false,'error'=>'Password too short']); exit; }

$hash = password_hash($new, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
$stmt->execute([$hash, $uid]);
echo json_encode(['ok' => true]);
