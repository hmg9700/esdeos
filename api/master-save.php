<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_login_or_redirect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../index.php?page=master'); exit;
}
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
  header('Location: ../index.php?page=master'); exit;
}

$type = $_POST['type'] ?? '';
$name = trim((string)($_POST['name'] ?? ''));
if ($name === '') { header('Location: ../index.php?page=master'); exit; }

$table = match ($type) {
  'line' => 'master_lines',
  'process' => 'master_processes',
  'item' => 'master_items',
  default => null
};
if (!$table) { header('Location: ../index.php?page=master'); exit; }

// ensure table exists
if ($table === 'master_lines') $pdo->exec("CREATE TABLE IF NOT EXISTS master_lines (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
if ($table === 'master_processes') $pdo->exec("CREATE TABLE IF NOT EXISTS master_processes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
if ($table === 'master_items') $pdo->exec("CREATE TABLE IF NOT EXISTS master_items (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) UNIQUE)");

$stmt = $pdo->prepare("INSERT IGNORE INTO {$table} (name) VALUES (?)");
$stmt->execute([$name]);

header('Location: ../index.php?page=master'); exit;
