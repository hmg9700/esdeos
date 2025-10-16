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

$title = trim($_POST['title'] ?? '');
$startDate = $_POST['start_date'] ?? '';
$startTime = $_POST['start_time'] ?? '00:00';
$endDate = $_POST['end_date'] ?? '';
$endTime = $_POST['end_time'] ?? '';

$location = trim($_POST['location'] ?? '');
$pic = trim($_POST['pic'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($title === '' || $startDate === '') {
  echo json_encode(['ok' => false, 'error' => 'Title and start date are required']);
  exit;
}

$startAt = $startDate . ' ' . ($startTime ?: '00:00') . ':00';
$endAt = null;
if ($endDate !== '') {
  $et = $endTime ?: '23:59';
  $endAt = $endDate . ' ' . $et . ':00';
}

try {
  $ins = $pdo->prepare("
    INSERT INTO activities (title, start_at, end_at, location, pic, notes, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $ins->execute([
    $title, $startAt, $endAt, $location ?: null, $pic ?: null, $notes ?: null,
    $_SESSION['user']['id'] ?? null
  ]);
  $newId = (int)$pdo->lastInsertId();
  echo json_encode(['ok' => true, 'id' => $newId]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Database error']);
}
