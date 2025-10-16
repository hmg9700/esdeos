<?php $csrf = ensure_csrf_token(); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0"><i class="fa-regular fa-calendar me-2"></i> Schedule</h1>
</div>

<p class="text-muted small mb-3">
  Anyone can view the calendar. <?php if (current_user()): ?>
  You are logged in — click a date to add events, click an event to delete.
  <?php else: ?>
  Login to add or delete events.
  <?php endif; ?>
</p>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<div id="calendar" data-logged-in="<?= current_user() ? '1' : '0' ?>"></div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="assets/js/schedule.js"></script>

<?php if (!current_user()): ?>
  <div class="alert alert-warning">
    <i class="fa-regular fa-circle-question me-1"></i>
    Please login to create schedules.
  </div>
<?php else: ?>
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form class="row g-3" method="post" action="api/schedule_create.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input type="text" class="form-control" name="task_name" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Scheduled Date</label>
          <input type="date" class="form-control" name="scheduled_date" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Location</label>
          <input type="text" class="form-control" name="location" placeholder="Optional">
        </div>
        <div class="col-md-4">
          <label class="form-label">PIC</label>
          <input type="text" class="form-control" name="assigned_to" placeholder="Person In Charge">
        </div>
        <div class="col-md-4">
          <label class="form-label">Activity</label>
          <select class="form-select" name="activity">
            <option value="">None</option>
            <option>Daily</option>
            <option>Weekly</option>
            <option>Monthly</option>
            <option>Quarterly</option>
            <option>Semester</option>
            <option>Yearly</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Notes</label>
          <textarea class="form-control" name="notes" rows="3" placeholder="Optional"></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Create Schedule
          </button>
        </div>
      </form>
    </div>
  </div>

  <?php
    $cols = [];
    $stmtCols = $pdo->query("SHOW COLUMNS FROM schedules");
    foreach ($stmtCols->fetchAll(PDO::FETCH_ASSOC) as $c) $cols[$c['Field']] = true;
 
    $titleField = isset($cols['task_name']) ? 'task_name' : (isset($cols['title']) ? 'title' : null);
    if ($titleField === null) { $titleField = 'task_name'; } // safe fallback
 
    $fields = [$titleField, 'scheduled_date'];
    if (isset($cols['assigned_to'])) $fields[] = 'assigned_to';
    elseif (isset($cols['pic'])) $fields[] = 'pic';
    if (isset($cols['location'])) $fields[] = 'location';
    if (isset($cols['activity'])) $fields[] = 'activity';
    if (isset($cols['created_at'])) $fields[] = 'created_at';
 
    $sql = "SELECT " . implode(',', $fields) . " FROM schedules ORDER BY scheduled_date ASC LIMIT 10";
    $latest = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <div class="card shadow-sm">
    <div class="card-header bg-light">
      <strong><i class="fa-regular fa-calendar-days me-2"></i> Upcoming</strong>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>Title</th>
              <th>Date</th>
              <th>PIC</th>
              <th>Location</th>
              <th>Activity</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($latest)): ?>
              <tr><td colspan="6" class="text-center text-muted">No schedules yet.</td></tr>
            <?php else: foreach ($latest as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['task_name'] ?? ($row['title'] ?? '')) ?></td>
                <td><?= htmlspecialchars($row['scheduled_date'] ?? '') ?></td>
                <td><?= htmlspecialchars((string) (($row['assigned_to'] ?? null) ?? ($row['pic'] ?? ''))) ?></td>
                <td><?= htmlspecialchars((string) ($row['location'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($row['activity'] ?? '')) ?></td>
                <td><?= htmlspecialchars($row['created_at'] ?? '') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>
