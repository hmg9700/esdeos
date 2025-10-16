<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../index.php?page=login');
  exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
  header('Location: ../index.php?page=login&error=Invalid+session+token');
  exit;
}

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
  header('Location: ../index.php?page=login&error=Missing+credentials');
  exit;
}

$stmt = $pdo->prepare("SELECT id, email, password_hash, username, full_name, level, status FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
  header('Location: ../index.php?page=login&error=Invalid+email+or+password');
  exit;
}

if (($user['status'] ?? 'deactive') !== 'active') {
  header('Location: ../index.php?page=login&error=Account+pending+admin+approval');
  exit;
}

$_SESSION['user'] = [
  'id' => (int) $user['id'],
  'email' => $user['email'],
  'username' => $user['username'] ?? null,
  'full_name' => $user['full_name'] ?? null,
  'level' => $user['level'] ?? 'user',
  'status' => $user['status'] ?? 'active',
];
$_SESSION['user_id'] = (int) $user['id'];        // backward compatibility
$_SESSION['user_email'] = $user['email'];        // backward compatibility

header('Location: ../index.php?page=dashboard');
exit;
