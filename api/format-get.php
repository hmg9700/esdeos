<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$title = $_GET['title'] ?? '';
if ($title === '') { http_response_code(400); echo json_encode(['error' => 'Title required']); exit; }

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

$stmt = $pdo->prepare("SELECT id, line, process, item, spec, default_result FROM audit_format WHERE title = ? ORDER BY id ASC");
$stmt->execute([$title]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['rows' => $rows]);
