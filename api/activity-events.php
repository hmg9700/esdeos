<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

// Support optional range (FullCalendar can pass start/end), but list all if absent
$start = $_GET['start'] ?? null;
$end   = $_GET['end'] ?? null;

try {
  if ($start && $end) {
    $stmt = $pdo->prepare("SELECT id, title, start_at, end_at FROM activities WHERE start_at BETWEEN ? AND ? ORDER BY start_at ASC");
    $stmt->execute([$start, $end]);
  } else {
    $stmt = $pdo->query("SELECT id, title, start_at, end_at FROM activities ORDER BY start_at DESC LIMIT 500");
  }
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $events = array_map(function ($r) {
    return [
      'id' => (string)$r['id'],
      'title' => $r['title'],
      'start' => str_replace(' ', 'T', $r['start_at']),
      'end' => $r['end_at'] ? str_replace(' ', 'T', $r['end_at']) : null,
    ];
  }, $rows);
  echo json_encode($events);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([]);
}
