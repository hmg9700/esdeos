<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

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
");

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

$where = [];
$params = [];
if ($from) { $where[] = "DATE(created_at) >= ?"; $params[] = $from; }
if ($to) { $where[] = "DATE(created_at) <= ?"; $params[] = $to; }

$sql = "SELECT group_id, MIN(DATE(created_at)) AS day, MIN(title) AS title, MIN(line) AS line
        FROM result_audit " . (count($where) ? ("WHERE " . implode(' AND ', $where)) : '') . "
        GROUP BY group_id ORDER BY day DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['rows' => $rows]);
