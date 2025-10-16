<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function is_logged_in(): bool {
  return !empty($_SESSION['user_id']);
}

function require_login_api() {
  if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
  }
}

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function verify_csrf_or_die($token) {
  if (empty($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(400);
    die('Invalid CSRF token');
  }
}

function sanitize($str): string {
  return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function ensure_upload_dir(string $dir): void {
  if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
  }
  // add .htaccess to disable PHP execution
  $ht = $dir . '/.htaccess';
  if (!file_exists($ht)) {
    @file_put_contents($ht, "Options -Indexes\nphp_flag engine off\nRemoveHandler .php\n");
  }
}

function is_admin(): bool {
  return !empty($_SESSION['user']) && (($_SESSION['user']['level'] ?? '') === 'admin');
}

function require_admin_api() {
  if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
  }
}
// </CHANGE>
