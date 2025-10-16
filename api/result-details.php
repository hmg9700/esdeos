<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$group = $_GET['group'] ?? '';
if ($group === '') { http_response_code(400); echo json_encode(['error' => 'group required']); exit; }

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

$stmt = $pdo->prepare("SELECT * FROM result_audit WHERE group_id = ? ORDER BY id ASC");
$stmt->execute([$group]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ids = array_column($rows, 'id');
$photosMap = [];
if ($ids) {
  $in = implode(',', array_fill(0, count($ids), '?'));
  $ph = $pdo->prepare("SELECT row_id, path FROM result_audit_photos WHERE row_id IN ($in)");
  $ph->execute($ids);
  foreach ($ph->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $photosMap[$p['row_id']][] = ['path' => $p['path']];
  }
}

echo json_encode(['rows' => $rows, 'photos' => $photosMap]);
