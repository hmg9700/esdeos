<?php
require_once __DIR__ . '/../config/db.php';
$u = current_user();
if (!$u || ($u['level'] ?? '') !== 'admin') {
  http_response_code(403);
  echo '<div class="alert alert-danger">Forbidden: Admin only.</div>';
  return;
}
$csrf = ensure_csrf_token();
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h2 class="h5 mb-0">Manage Users</h2>
  <button class="btn btn-warning btn-sm" id="btnAddUser">
    <i class="fa-solid fa-user-plus me-1"></i> Add User
  </button>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle" id="usersTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Level</th>
            <th>Status</th>
            <th>Created</th>
            <th style="width: 120px;">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <form class="modal-content" id="userForm">
      <div class="modal-header">
        <h5 class="modal-title">User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="id" id="userId">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input class="form-control form-control-sm" name="username" id="username" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input class="form-control form-control-sm" name="full_name" id="full_name" required>
          </div>
          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control form-control-sm" name="email" id="email" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Level</label>
            <select class="form-select form-select-sm" name="level" id="level">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select form-select-sm" name="status" id="status">
              <option value="active">Active</option>
              <option value="deactive">Deactive</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Password (set/reset)</label>
            <input type="password" class="form-control form-control-sm" name="password" id="password" placeholder="Leave blank to keep current">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary btn-sm" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="assets/js/manage-users.js"></script>
