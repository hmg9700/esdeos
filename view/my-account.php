<?php
require_once __DIR__ . '/../config/db.php';
$u = current_user();
if (!$u) { header('Location: index.php?page=login'); exit; }
$csrf = ensure_csrf_token();
?>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header py-2"><strong>Profile</strong></div>
      <form class="card-body" id="profileForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-2">
          <label class="form-label">Username</label>
          <input class="form-control form-control-sm" name="username" value="<?= htmlspecialchars($u['username'] ?? '') ?>">
        </div>
        <div class="mb-2">
          <label class="form-label">Full Name</label>
          <input class="form-control form-control-sm" name="full_name" value="<?= htmlspecialchars($u['full_name'] ?? '') ?>">
        </div>
        <div class="mb-2">
          <label class="form-label">Email</label>
          <input type="email" class="form-control form-control-sm" name="email" value="<?= htmlspecialchars($u['email']) ?>">
        </div>
        <button class="btn btn-warning btn-sm">Save Profile</button>
      </form>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header py-2"><strong>Change Password</strong></div>
      <form class="card-body" id="pwdForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-2">
          <label class="form-label">New Password</label>
          <input type="password" class="form-control form-control-sm" name="new_password" required>
        </div>
        <button class="btn btn-warning btn-sm">Update Password</button>
      </form>
    </div>
  </div>
</div>
<script>
document.getElementById('profileForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await fetch('api/my-account-save.php', { method: 'POST', body: new FormData(e.target) });
  const j = await res.json();
  alert(j.ok ? 'Saved' : (j.error || 'Failed'));
  if (j.ok) location.reload();
});
document.getElementById('pwdForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await fetch('api/change-password.php', { method: 'POST', body: new FormData(e.target) });
  const j = await res.json();
  alert(j.ok ? 'Password updated' : (j.error || 'Failed'));
});
</script>
