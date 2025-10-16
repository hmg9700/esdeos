<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!current_user()) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Unauthorized']);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
  exit;
}
$token = $_POST['csrf'] ?? '';
if (!verify_csrf($token)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid CSRF']);
  exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid id']);
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
  $stmt->execute([$id]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error']);
}
