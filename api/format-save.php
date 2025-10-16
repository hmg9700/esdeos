<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}
header('Content-Type: application/json');

try {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS audit_format (
      id INT AUTO_INCREMENT PRIMARY KEY,
      title VARCHAR(255) NOT NULL,
      line VARCHAR(255) NOT NULL,
      process VARCHAR(255) NOT NULL,
      item VARCHAR(255) NOT NULL,
      spec VARCHAR(255) DEFAULT NULL,
      default_result ENUM('OK','NG') DEFAULT 'OK',
      creator VARCHAR(255) DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX(title)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  $title = trim($_POST['title'] ?? '');
  $creator = trim($_POST['creator'] ?? ($_SESSION['user']['email'] ?? ($_SESSION['user_email'] ?? '')));
  if ($title === '') throw new Exception('Title required');

  $lines = $_POST['line'] ?? [];
  $processes = $_POST['process'] ?? [];
  $items = $_POST['item'] ?? [];
  $specs = $_POST['spec'] ?? [];
  $defaults = $_POST['default_result'] ?? [];

  $stmt = $pdo->prepare("INSERT INTO audit_format (title, line, process, item, spec, default_result, creator) VALUES (?,?,?,?,?,?,?)");

  $count = max(count($lines), count($processes), count($items));
  for ($i=0; $i<$count; $i++) {
    $line = trim($lines[$i] ?? '');
    $proc = trim($processes[$i] ?? '');
    $item = trim($items[$i] ?? '');
    $spec = trim($specs[$i] ?? '');
    $def = in_array(($defaults[$i] ?? 'OK'), ['OK','NG']) ? $defaults[$i] : 'OK';
    if ($line === '' || $proc === '' || $item === '') continue;
    $stmt->execute([$title, $line, $proc, $item, $spec, $def, $creator]);
  }

  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['error' => $e->getMessage()]);
}
