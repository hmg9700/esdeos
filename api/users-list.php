<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
header('Content-Type: application/json');
require_admin_api();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
  $stmt = $pdo->prepare("SELECT id, username, full_name, email, level, status, created_at FROM users WHERE id = ?");
  $stmt->execute([$id]);
  echo json_encode(['ok' => true, 'user' => $stmt->fetch()]);
  exit;
}

$stmt = $pdo->query("SELECT id, username, full_name, email, level, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
echo json_encode(['ok' => true, 'users' => $users]);
