<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

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

$sql = "SELECT title, COUNT(*) as count, MIN(creator) as creator, MIN(created_at) as created_at
        FROM audit_format GROUP BY title ORDER BY created_at DESC";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['rows' => $rows]);
