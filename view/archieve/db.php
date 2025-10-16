<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Adjust these for your local setup
$dbHost = '127.0.0.1';
$dbName = 'esdeos';
$dbUser = 'root';
$dbPass = ''; // default XAMPP

$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  // Create DB if missing (connect without db first)
  $pdoAdmin = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass, $options);
  $pdoAdmin->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

  $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (Throwable $e) {
  http_response_code(500);
  die("Database connection error.");
}

// CSRF helpers
function ensure_csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Auth helpers
function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function require_login_or_redirect(): void {
  if (!isset($_SESSION['user'])) {
    header('Location: /index.php?page=login');
    exit;
  }
}

// Auto-migrate schema (idempotent)
function migrate(PDO $pdo): void {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      email VARCHAR(191) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS esd_checks (
      id INT AUTO_INCREMENT PRIMARY KEY,
      asset_tag VARCHAR(100) NOT NULL,
      location VARCHAR(191) NOT NULL,
      result ENUM('pass','fail') NOT NULL,
      remarks TEXT NULL,
      created_by INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (asset_tag),
      CONSTRAINT fk_esd_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS audits (
      id INT AUTO_INCREMENT PRIMARY KEY,
      area VARCHAR(191) NOT NULL,
      auditor VARCHAR(191) NOT NULL,
      findings TEXT NULL,
      created_by INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_audit_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS schedules (
      id INT AUTO_INCREMENT PRIMARY KEY,
      task_name VARCHAR(191) NOT NULL,
      scheduled_date DATE NOT NULL,
      assigned_to VARCHAR(191) NULL,
      notes TEXT NULL,
      created_by INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_schedule_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  // New tables for checksheet rows and photos
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS esd_checksheet_rows (
      id INT AUTO_INCREMENT PRIMARY KEY,
      line VARCHAR(191) NOT NULL,
      process VARCHAR(191) NOT NULL,
      item VARCHAR(255) NOT NULL,
      result ENUM('OK','NG') NOT NULL,
      value_text VARCHAR(191) NULL,
      created_by INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_checksheet_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS esd_checksheet_photos (
      id INT AUTO_INCREMENT PRIMARY KEY,
      row_id INT NOT NULL,
      file_path VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_checksheet_row FOREIGN KEY (row_id) REFERENCES esd_checksheet_rows(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  // Add activities table for Calendar/Activity page
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS activities (
      id INT AUTO_INCREMENT PRIMARY KEY,
      title VARCHAR(191) NOT NULL,
      start_at DATETIME NOT NULL,
      end_at DATETIME NULL,
      location VARCHAR(191) NULL,
      pic VARCHAR(191) NULL,
      notes TEXT NULL,
      created_by INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (start_at),
      CONSTRAINT fk_activity_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  // Attachments for activities
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS activity_files (
      id INT AUTO_INCREMENT PRIMARY KEY,
      activity_id INT NOT NULL,
      description VARCHAR(255) NULL,
      filename VARCHAR(255) NOT NULL,
      size BIGINT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_activity_files FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");
}

// Extend users table with requested columns if missing
try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(100) NULL UNIQUE"); } catch (Throwable $e) {}
try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS full_name VARCHAR(191) NULL"); } catch (Throwable $e) {}
try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS level ENUM('admin','user') NOT NULL DEFAULT 'user'"); } catch (Throwable $e) {}
try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active','deactive') NOT NULL DEFAULT 'deactive'"); } catch (Throwable $e) {}

migrate($pdo);

// Seed default admin if none
$stmt = $pdo->query("SELECT COUNT(*) AS c FROM users");
$count = (int) $stmt->fetch()['c'];
if ($count === 0) {
  $email = 'admin@example.com';
  $pass = 'admin123';
  $hash = password_hash($pass, PASSWORD_BCRYPT);
  $ins = $pdo->prepare("INSERT INTO users (email, password_hash, level, status, full_name, username) VALUES (?, ?, 'admin', 'active', 'Administrator', 'admin')");
  $ins->execute([$email, $hash]);
}
