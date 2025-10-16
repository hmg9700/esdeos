<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) { http_response_code(401); echo 'Unauthorized'; exit; }

$pdo->exec("
  CREATE TABLE IF NOT EXISTS result_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(64) NOT NULL,
    title VARCHAR(255) NOT NULL,
    line VARCHAR(255) NOT NULL,
    process VARCHAR(255) NOT NULL,
    item VARCHAR(255) NOT NULL,
    spec VARCHAR(255) DEFAULT NULL,
    result ENUM('OK','NG') NOT NULL,
    creator VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(group_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  CREATE TABLE IF NOT EXISTS result_audit_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_id INT NOT NULL,
    path VARCHAR(512) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (row_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$title = trim($_POST['title'] ?? '');
$creator = trim($_POST['creator'] ?? ($_SESSION['user']['email'] ?? ($_SESSION['user_email'] ?? '')));
$lines = $_POST['line'] ?? [];
$processes = $_POST['process'] ?? [];
$items = $_POST['item'] ?? [];
$specs = $_POST['spec'] ?? [];
$results = $_POST['result'] ?? [];
if ($title === '') { http_response_code(400); echo 'Title required'; exit; }

$group = bin2hex(random_bytes(8));

$ins = $pdo->prepare("INSERT INTO result_audit (group_id, title, line, process, item, spec, result, creator) VALUES (?,?,?,?,?,?,?,?)");
for ($i=0; $i<count($lines); $i++) {
  $line = trim($lines[$i] ?? '');
  $proc = trim($processes[$i] ?? '');
  $item = trim($items[$i] ?? '');
  $spec = trim($specs[$i] ?? '');
  $resv = in_array(($results[$i] ?? 'OK'), ['OK','NG']) ? $results[$i] : 'OK';
  if ($line === '' || $proc === '' || $item === '') continue;

  $ins->execute([$group, $title, $line, $proc, $item, $spec, $resv, $creator]);
  $rowId = (int)$pdo->lastInsertId();

  // Photos for row i
  if (!empty($_FILES['photos']) && isset($_FILES['photos']['name'][$i])) {
    $count = count($_FILES['photos']['name'][$i]);
    $baseDir = __DIR__ . '/../uploads/audit/' . $group;
    if (!is_dir($baseDir)) @mkdir($baseDir, 0777, true);
    $ps = $pdo->prepare("INSERT INTO result_audit_photos (row_id, path) VALUES (?,?)");
    for ($k=0; $k<$count; $k++) {
      $tmp = $_FILES['photos']['tmp_name'][$i][$k] ?? null;
      $name = $_FILES['photos']['name'][$i][$k] ?? null;
      if (!$tmp || !$name) continue;
      $ext = pathinfo($name, PATHINFO_EXTENSION);
      $safe = uniqid('p_', true) . '.' . strtolower($ext);
      $dest = $baseDir . '/' . $safe;
      if (move_uploaded_file($tmp, $dest)) {
        $url = 'uploads/audit/' . $group . '/' . $safe;
        $ps->execute([$rowId, $url]);
      }
    }
  }
}

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'group_id' => $group]);
