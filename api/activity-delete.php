<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
  echo json_encode(['ok' => false, 'error' => 'Login required']);
  exit;
}

$token = $_POST['csrf'] ?? '';
if (!verify_csrf($token)) {
  echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
  exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['ok' => false, 'error' => 'Invalid id']);
  exit;
}

try {
  $del = $pdo->prepare("DELETE FROM activities WHERE id = ?");
  $del->execute([$id]);
  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Database error']);
}
