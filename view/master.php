<?php
$csrf = ensure_csrf_token();
if (!current_user()) {
  echo '<div class="container py-4"><div class="alert alert-info">Login required.</div></div>';
  return;
}
$pdo->exec("CREATE TABLE IF NOT EXISTS master_lines (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
$pdo->exec("CREATE TABLE IF NOT EXISTS master_processes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
$pdo->exec("CREATE TABLE IF NOT EXISTS master_items (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) UNIQUE)");
$lines = $pdo->query("SELECT id,name FROM master_lines ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$procs = $pdo->query("SELECT id,name FROM master_processes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$items = $pdo->query("SELECT id,name FROM master_items ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container py-4">
  <h3 class="mb-4">Data Master</h3>
  <div class="row g-4">
    <div class="col-md-4">
      <h5>Lines</h5>
      <!-- POST to unified master-save.php with CSRF -->
      <form class="d-flex gap-2 mb-2" action="api/master-save.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="type" value="line">
        <input class="form-control" name="name" placeholder="New line" required>
        <button class="btn btn-warning">Add</button>
      </form>
      <ul class="list-group">
        <?php foreach ($lines as $l): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($l['name']) ?>
            <!-- Delete via POST with CSRF (no GET delete) -->
            <form action="api/master-delete.php" method="post" class="mb-0">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="type" value="line">
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="col-md-4">
      <h5>Processes</h5>
      <form class="d-flex gap-2 mb-2" action="api/master-save.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="type" value="process">
        <input class="form-control" name="name" placeholder="New process" required>
        <button class="btn btn-warning">Add</button>
      </form>
      <ul class="list-group">
        <?php foreach ($procs as $p): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($p['name']) ?>
            <form action="api/master-delete.php" method="post" class="mb-0">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="type" value="process">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="col-md-4">
      <h5>Items</h5>
      <form class="d-flex gap-2 mb-2" action="api/master-save.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="type" value="item">
        <input class="form-control" name="name" placeholder="New item" required>
        <button class="btn btn-warning">Add</button>
      </form>
      <ul class="list-group">
        <?php foreach ($items as $i): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($i['name']) ?>
            <form action="api/master-delete.php" method="post" class="mb-0">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="type" value="item">
              <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
