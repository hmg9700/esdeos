<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$loggedIn = isset($_SESSION['user']) || isset($_SESSION['user_id']);
?>
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 m-0">Manage Check Sheet</h1>
    <?php if (!$loggedIn): ?>
      <span class="badge bg-secondary">Login required to save</span>
    <?php endif; ?>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form id="format-form">
        <div class="row g-3 mb-2">
          <div class="col-12 col-md-6">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" placeholder="e.g. Audit ESD Line A Weekly" required />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Creator</label>
            <input type="text" class="form-control" name="creator" placeholder="Your name" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? ($_SESSION['user_email'] ?? '')) ?>" />
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle" id="format-table">
            <thead class="table-light">
              <tr>
                <th style="width: 18%">Line</th>
                <th style="width: 22%">Process</th>
                <th style="width: 24%">Item</th>
                <th style="width: 18%">Spec</th>
                <th style="width: 10%">Default</th>
                <th style="width: 8%"></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-add-row">
            <i class="fa fa-plus"></i> Add Row
          </button>
          <button type="submit" class="btn btn-warning btn-sm" <?= $loggedIn ? '' : 'disabled' ?>>
            <i class="fa fa-save"></i> Save Format
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <strong>Existing Formats</strong>
    </div>
    <div class="card-body">
      <div id="formats-list" class="table-responsive"></div>
    </div>
  </div>
</div>

<script src="assets/js/manage-checksheet.js"></script>
