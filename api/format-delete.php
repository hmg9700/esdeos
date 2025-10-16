<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
header('Content-Type: application/json');
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
  http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
}
$title = $_POST['title'] ?? '';
if ($title === '') { http_response_code(400); echo json_encode(['error' => 'Title required']); exit; }

$stmt = $pdo->prepare("DELETE FROM audit_format WHERE title = ?");
$stmt->execute([$title]);
echo json_encode(['ok' => true, 'deleted' => $stmt->rowCount()]);
?>
