<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';

$page = $_GET['page'] ?? 'dashboard';

$validPages = [
  'dashboard' => __DIR__ . '/view/dashboard.php',
  'esd_checker_log' => __DIR__ . '/view/esd_checker_log.php', 
  'esd_checker_summary' => __DIR__ . '/view/esd_checker_summary.php',
  'manage-checksheet' => __DIR__ . '/view/manage-checksheet.php',
  'checksheet' => __DIR__ . '/view/checksheet.php',
  'audit-summary' => __DIR__ . '/view/audit-summary.php',
  'audit-view' => __DIR__ . '/view/audit-view.php',
  'schedule' => __DIR__ . '/view/schedule.php',
  'activity' => __DIR__ . '/view/activity.php', // register new Calendar/Activity page
  'master' => __DIR__ . '/view/master.php',
  'login' => __DIR__ . '/view/login.php',
  'register' => __DIR__ . '/view/register.php', // self signup
  'my-account' => __DIR__ . '/view/my-account.php',
  'manage-users' => __DIR__ . '/view/manage-users.php', // admin page
  'master' => __DIR__ . '/view/master.php',
];

function is_logged_in(): bool {
  return isset($_SESSION['user']);
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1"
    >
    <title>ESD-EOS Management</title>
    <!-- Bootstrap CSS (CDN) -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    >
    <!-- Font Awesome (CDN) -->
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
      rel="stylesheet"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="assets/style/style.css">
  </head>
  <body>
    <?php include __DIR__ . '/view/partials/navbar.php'; ?>

    <main class="container py-4">
      <?php
        if (array_key_exists($page, $validPages)) {
          include $validPages[$page];
        } else {
          http_response_code(404);
          echo '<div class="alert alert-danger">Page not found.</div>';
        }
      ?>
    </main>

    <!-- Bootstrap JS (CDN) -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <script src="assets/js/app.js"></script>
    <script>window.CSRF_TOKEN = '<?= isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : '' ?>';</script>
  </body>
</html>
