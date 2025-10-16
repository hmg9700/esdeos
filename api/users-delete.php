<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
header('Content-Type: application/json');
require_admin_api();

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid CSRF']);
  exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['ok' => false, 'error' => 'Invalid user id']);
  exit;
}
if (!empty($_SESSION['user']) && (int)$_SESSION['user']['id'] === $id) {
  echo json_encode(['ok' => false, 'error' => 'You cannot delete your own account.']);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id]);
echo json_encode(['ok' => true]);
