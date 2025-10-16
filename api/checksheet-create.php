<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// Ensure storage tables exist before insert
$pdo->exec("
  CREATE TABLE IF NOT EXISTS esd_checksheet_rows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    line VARCHAR(128) NOT NULL,
    process VARCHAR(128) NOT NULL,
    item VARCHAR(255) NOT NULL,
    result ENUM('OK','NG') NOT NULL DEFAULT 'NG',
    value_text VARCHAR(255) NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
");
$pdo->exec("
  CREATE TABLE IF NOT EXISTS esd_checksheet_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (row_id)
  )
");

if (!current_user()) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Unauthorized']);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
  exit;
}
$token = $_POST['csrf'] ?? '';
if (!verify_csrf($token)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid CSRF']);
  exit;
}

$rows = $_POST['row_index'] ?? [];
if (!is_array($rows) || count($rows) === 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'No rows']);
  exit;
}

$baseDir = __DIR__ . '/../uploads/checksheet';
if (!is_dir($baseDir)) {
  @mkdir($baseDir, 0755, true);
  @file_put_contents($baseDir . '/.htaccess', "Options -Indexes\nphp_flag engine off\nRemoveHandler .php\n");
}

try {
  $pdo->beginTransaction();
  $stmtRow = $pdo->prepare("INSERT INTO esd_checksheet_rows (line, process, item, result, value_text, created_by) VALUES (?, ?, ?, ?, ?, ?)");
  $stmtPic = $pdo->prepare("INSERT INTO esd_checksheet_photos (row_id, file_path) VALUES (?, ?)");

  foreach ($rows as $idx) {
    $i = (int)$idx;
    $line = trim((string)($_POST['line'][$i] ?? ''));
    $proc = trim((string)($_POST['process'][$i] ?? ''));
    $item = trim((string)($_POST['item'][$i] ?? ''));
    $res  = trim((string)($_POST['result'][$i] ?? 'NG'));
    $val  = trim((string)($_POST['value'][$i] ?? ''));

    $stmtRow->execute([$line, $proc, $item, $res === 'OK' ? 'OK' : 'NG', $val, current_user()['id'] ?? null]);
    $rowId = (int)$pdo->lastInsertId();

    $key = "photos_{$i}";
    if (!empty($_FILES[$key]) && is_array($_FILES[$key]['name'])) {
      $names = $_FILES[$key]['name'];
      $tmps  = $_FILES[$key]['tmp_name'];
      $errs  = $_FILES[$key]['error'];

      foreach ($names as $k => $orig) {
        if (($errs[$k] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
        $tmp = $tmps[$k] ?? null;
        if (!$tmp || !is_uploaded_file($tmp)) continue;

        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) continue;

        $new = $rowId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $baseDir . '/' . $new;
        if (move_uploaded_file($tmp, $dest)) {
          $stmtPic->execute([$rowId, 'uploads/checksheet/' . $new]);
        }
      }
    }
  }

  $pdo->commit();
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error']);
}
