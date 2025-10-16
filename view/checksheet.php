<?php $csrf = ensure_csrf_token(); ?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <h1 class="h4 mb-0"><i class="fa-solid fa-list-check me-2"></i> Checksheet Audit</h1>
</div>

<?php if (!current_user()): ?>
  <div class="alert alert-warning mb-3">
    <i class="fa-regular fa-circle-question me-1"></i>
    Anyone can view this page. Please login to input new data.
  </div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form id="checksheetForm" class="table-responsive" method="post" action="api/result-save.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

      <div class="row g-2 mb-3">
        <div class="col-sm-6">
          <label class="form-label">Title</label>
          <input id="titleInput" name="title" class="form-control" placeholder="Type or set from Format" required>
        </div>
        <div class="col-sm-6">
          <label class="form-label">Creator</label>
          <input id="creatorInput" name="creator" class="form-control" placeholder="Your name" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
        </div>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-sm-8">
          <label class="form-label">Format Title</label>
          <div class="input-group">
            <select id="formatTitle" class="form-select">
              <option value="">Select Title</option>
               
            </select>
            <button type="button" id="btnLoadFormat" class="btn btn-outline-secondary">
              <i class="fa-solid fa-download me-1"></i> Get Format
            </button>
            <button type="button" id="btnClearRows" class="btn btn-outline-secondary">
              <i class="fa-solid fa-broom me-1"></i> Clear Rows
            </button>
          </div>
          <div class="form-text">Pick a saved format and click Get Format, or just type a Title above and input rows manually.</div>
        </div>
      </div>

      <div class="d-flex gap-2 mb-3">
        <button type="button" id="btnAddRow" class="btn btn-sm btn-primary" <?= current_user() ? '' : 'disabled' ?>>
          <i class="fa-solid fa-plus me-1"></i> Add Row
        </button>
        <?php if (!current_user()): ?>
          <span class="text-muted small">Login to add and save rows.</span>
        <?php else: ?>
          <span class="text-muted small">Pick a Format Title and click Get Format to populate rows. You may add extra rows if needed.</span>
        <?php endif; ?>
        <span class="text-muted small">If dropdowns are empty, add Line/Process/Item in <a href="index.php?page=master">Data Master</a>, then refresh.</span>
      </div>

      <table class="table table-sm align-middle" id="checksheetTable">
        <thead class="table-light">
          <tr>
            <th style="min-width:120px">Line</th>
            <th style="min-width:160px">Process</th>
            <th style="min-width:200px">Item</th>
            <th style="min-width:120px">Result</th>
            <th style="min-width:140px">Value</th>
            <th style="min-width:220px">Picture(s)</th>
            <th class="text-end" style="width:60px">Action</th>
          </tr>
        </thead>
        <tbody>
           rows added dynamically 
        </tbody>
      </table>

      <div class="mt-3">
        <button class="btn btn-success" <?= current_user() ? '' : 'disabled' ?>>
          <i class="fa-solid fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

<script src="assets/js/checksheet.js"></script>
