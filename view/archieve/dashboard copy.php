<?php
// fetch quick stats
$counts = [
  'checks' => (int) $pdo->query("SELECT COUNT(*) AS c FROM esd_checks")->fetch()['c'],
  'audits' => (int) $pdo->query("SELECT COUNT(*) AS c FROM audits")->fetch()['c'],
  'schedules' => (int) $pdo->query("SELECT COUNT(*) AS c FROM schedules")->fetch()['c'],
];
$recentChecks = $pdo->query("SELECT asset_tag, location, result, created_at FROM esd_checks ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0">
    <i class="fa-solid fa-gauge-high me-2"></i> ESD Checker Dashboard
  </h1>
</div>

<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">ESD Checks</div>
          <div class="fs-3 fw-bold"><?= $counts['checks'] ?></div>
        </div>
        <i class="fa-solid fa-microchip fs-2 text-primary"></i>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Audits</div>
          <div class="fs-3 fw-bold"><?= $counts['audits'] ?></div>
        </div>
        <i class="fa-solid fa-clipboard-check fs-2 text-success"></i>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Schedules</div>
          <div class="fs-3 fw-bold"><?= $counts['schedules'] ?></div>
        </div>
        <i class="fa-regular fa-calendar-days fs-2 text-warning"></i>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-light">
    <strong><i class="fa-solid fa-clock-rotate-left me-2"></i> Recent ESD Checks</strong>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>Asset Tag</th>
            <th>Location</th>
            <th>Result</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentChecks)): ?>
            <tr><td colspan="4" class="text-center text-muted">No records yet.</td></tr>
          <?php else: foreach ($recentChecks as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['asset_tag']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td>
                <span class="badge <?= $row['result'] === 'pass' ? 'text-bg-success' : 'text-bg-danger' ?>">
                  <?= strtoupper($row['result']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
