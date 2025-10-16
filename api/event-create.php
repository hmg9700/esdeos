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

$title = trim((string)($_POST['title'] ?? ''));
$start = trim((string)($_POST['start'] ?? ''));

if ($title === '' || $start === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Missing title or start']);
  exit;
}

try {
  $stmt = $pdo->prepare("INSERT INTO schedules (task_name, scheduled_date, created_by) VALUES (?, ?, ?)");
  $stmt->execute([$title, $start, current_user()['id'] ?? null]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error']);
}
