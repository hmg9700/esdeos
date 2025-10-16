<?php
// fetch quick stats
$counts = [
  'checks' => (int) $pdo->query("SELECT COUNT(DISTINCT(machine_location)) AS c FROM log;")->fetch()['c'],
  'audits' => (int) $pdo->query("SELECT COUNT(*) AS c FROM activities Where start_at < NOW()")->fetch()['c'],
  'Events' => (int) $pdo->query("SELECT COUNT(*) AS c FROM activities Where start_at >= NOW()")->fetch()['c'],
];
$ESDchecker = $pdo->query("SELECT machine_location, system_type FROM log Group by machine_location ORDER BY machine_location ASC")->fetchAll();
$recentChecks = $pdo->query("SELECT title, start_at, end_at, location, pic, notes, created_at FROM activities Where start_at > NOW() ORDER BY start_at ASC LIMIT 5")->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <h1 class="h4 mb-0">
    <i class="fa-solid"></i> ESD-EOS Management
  </h1>
</div>

<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">ESD Checker (Station)</div>
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
          <div class="text-muted small">Events Finished</div>
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
          <div class="text-muted small">Upcoming Events</div>
          <div class="fs-3 fw-bold"><?= $counts['Events'] ?></div>
        </div>
        <i class="fa-regular fa-calendar-days fs-2 text-warning"></i>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    </div>
  <div class="col-12 col-md-4">
    </div>
  <div class="col-12 col-md-4">
    </div>
</div>

<div class="row g-3 mb-4">  
  <div class="col-12 col-md-4">
    <div class="card shadow-sm">
      <div class="card-header bg-light">
        <strong><i class="fa-solid fa-microchip text-secondary"></i> ESD Checker (Station)</strong>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0"> <thead> 
              <tr>
                <th style="text-align:center;">No</th>
                <th style="text-align:center;">Location</th>
                <th style="text-align:center;">System Type</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($ESDchecker)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted">No records yet.</td>
                </tr>
              <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($ESDchecker as $row): ?>
                  <tr>
                    <td style="text-align:center;"><?= $no++ ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['machine_location'] ?? '') ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['system_type'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
    
  <div class="col-12 col-md-8">
    <div class="card shadow-sm">
      <div class="card-header bg-light">
        <strong><i class="fa-regular fa-calendar-days text-primary"></i> Upcoming Events</strong>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th style="text-align:center;">No</th>
                <th style="text-align:center;">Title</th>
                <th style="text-align:center;">Event Start</th>
                <th style="text-align:center;">Location</th>
                <th style="text-align:center;">PIC</th>
                <th style="text-align:center;">notes</th>
                <th style="text-align:center;">created_at</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentChecks)): ?>
                <tr>
                  <td colspan="9" class="text-center text-muted">No records yet.</td> 
                </tr>
              <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($recentChecks as $row): ?>
                  <tr>
                    <td style="text-align:center;"><?= $no++ ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['title'] ?? '') ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['start_at'] ?? '') ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['location'] ?? '') ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['pic'] ?? '') ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['notes'] ?? '') ?></td>
                    <td style="text-align:center;"><?= htmlspecialchars($row['created_at'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
    
</div>