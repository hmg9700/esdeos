<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
header('Content-Type: application/json');
require_admin_api();

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid CSRF']);
  exit;
}

$id = (int)($_POST['id'] ?? 0);
$username = trim((string)($_POST['username'] ?? ''));
$full_name = trim((string)($_POST['full_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$level = ($_POST['level'] ?? 'user') === 'admin' ? 'admin' : 'user';
$status = ($_POST['status'] ?? 'deactive') === 'active' ? 'active' : 'deactive';
$pwd = (string)($_POST['password'] ?? '');

try {
  if ($id > 0) {
    if ($pwd !== '') {
      $hash = password_hash($pwd, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, level=?, status=?, password_hash=? WHERE id=?");
      $stmt->execute([$username, $full_name, $email, $level, $status, $hash, $id]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, level=?, status=? WHERE id=?");
      $stmt->execute([$username, $full_name, $email, $level, $status, $id]);
    }
  } else {
    $hash = $pwd !== '' ? password_hash($pwd, PASSWORD_BCRYPT) : password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, level, status, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $full_name, $email, $level, $status, $hash]);
  }
  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Email or username may already exist.']);
}
