<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid id']);
  exit;
}

try {
  $stmt = $pdo->prepare("SELECT id, title, start_at, end_at, location, pic, notes FROM activities WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
  }
  $fs = $pdo->prepare("SELECT id, description, filename, size FROM activity_files WHERE activity_id=? ORDER BY id DESC");
  $fs->execute([$id]);
  $row['files'] = $fs->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($row);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error']);
}
