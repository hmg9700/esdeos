<?php
declare(strict_types=1);
$user = current_user();
$csrf = ensure_csrf_token();
?>
<div class="row g-4">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Calendar / Activity</h5>
      <button type="button" class="btn btn-warning" id="activity-add-btn">
        <i class="fa-solid fa-plus me-1"></i> Add Activity
      </button>
    </div>
    <div class="card">
      <div class="card-body">
        <div id="activity-calendar"
             data-logged-in="<?= $user ? '1' : '0' ?>"
             class="min-vh-50"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for viewing/adding/editing an activity with attachments -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="activity-modal-form">
        <div class="modal-header">
          <h5 class="modal-title" id="activityModalLabel">Activity Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="id" id="am-id">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Title</label>
              <input class="form-control" name="title" id="am-title" <?= $user ? '' : 'disabled' ?>>
            </div>
            <div class="col-md-4">
              <label class="form-label">PIC</label>
              <input class="form-control" name="pic" id="am-pic" <?= $user ? '' : 'disabled' ?>>
            </div>
            <div class="col-md-6">
              <label class="form-label">Start</label>
              <div class="d-flex gap-2">
                <input type="date" class="form-control" name="start_date" id="am-start-date" <?= $user ? '' : 'disabled' ?>>
                <input type="time" class="form-control" name="start_time" id="am-start-time" <?= $user ? '' : 'disabled' ?>>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">End</label>
              <div class="d-flex gap-2">
                <input type="date" class="form-control" name="end_date" id="am-end-date" <?= $user ? '' : 'disabled' ?>>
                <input type="time" class="form-control" name="end_time" id="am-end-time" <?= $user ? '' : 'disabled' ?>>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Location</label>
              <input class="form-control" name="location" id="am-location" <?= $user ? '' : 'disabled' ?>>
            </div>
            <div class="col-md-6">
              <label class="form-label">Notes</label>
              <input class="form-control" name="notes" id="am-notes" <?= $user ? '' : 'disabled' ?>>
            </div>
          </div>

          <hr class="my-3" />
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-2">Attachments</h6>
            <?php if ($user): ?>
            <button class="btn btn-outline-primary btn-sm" type="button" id="am-add-file-btn">
              <i class="fa-solid fa-paperclip me-1"></i> Add file
            </button>
            <?php endif; ?>
          </div>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th style="width: 45%">Description</th>
                  <th style="width: 35%">Filename</th>
                  <th style="width: 10%">Size</th>
                  <th style="width: 10%">Action</th>
                </tr>
              </thead>
              <tbody id="am-files-body">
                 rows rendered by JS 
              </tbody>
            </table>
          </div>
          <p class="text-muted small">You can upload multiple files. Uploaded files will appear in this list.</p>
        </div>
        <div class="modal-footer">
          <?php if ($user): ?>
            <button type="button" class="btn btn-danger me-auto" id="activity-delete-btn">
              <i class="fa-solid fa-trash-can me-1"></i> Delete
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save changes
            </button>
          <?php else: ?>
            <span class="text-muted small">Login required to add, edit, or delete.</span>
          <?php endif; ?>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- FullCalendar CSS/JS (CDN) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="assets/js/activity.js"></script>
