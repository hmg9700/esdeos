<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

try {
  $stmt = $pdo->query("SELECT id, task_name AS title, scheduled_date AS start FROM schedules ORDER BY scheduled_date ASC");
  $events = $stmt->fetchAll();
  echo json_encode($events);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([]);
}
