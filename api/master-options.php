<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$pdo->exec("CREATE TABLE IF NOT EXISTS master_lines (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
$pdo->exec("CREATE TABLE IF NOT EXISTS master_processes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
$pdo->exec("CREATE TABLE IF NOT EXISTS master_items (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) UNIQUE)");

$lines = $pdo->query("SELECT id, name FROM master_lines ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$procs = $pdo->query("SELECT id, name FROM master_processes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$items = $pdo->query("SELECT id, name FROM master_items ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['lines' => $lines, 'processes' => $procs, 'items' => $items]);
