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

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$startDate = $_POST['start_date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$location = trim($_POST['location'] ?? '');
$pic = trim($_POST['pic'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($id <= 0 || $title === '' || $startDate === '') {
  echo json_encode(['ok' => false, 'error' => 'Missing required fields']);
  exit;
}

$startAt = $startDate . ' ' . (($startTime ?: '00:00') . ':00');
$endAt = null;
if ($endDate !== '') {
  $endAt = $endDate . ' ' . (($endTime ?: '23:59') . ':00');
}

try {
  $sql = "UPDATE activities
          SET title = ?, start_at = ?, end_at = ?, location = ?, pic = ?, notes = ?
          WHERE id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $title,
    $startAt,
    $endAt,
    $location !== '' ? $location : null,
    $pic !== '' ? $pic : null,
    $notes !== '' ? $notes : null,
    $id
  ]);
  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Database error']);
}
