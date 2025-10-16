<?php
require_once __DIR__ . '/../config/db.php';
$csrf = ensure_csrf_token();
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card">
      <div class="card-header py-2"><strong>Register</strong></div>
      <form class="card-body" id="regForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-2">
          <label class="form-label">Username</label>
          <input class="form-control form-control-sm" name="username" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Full Name</label>
          <input class="form-control form-control-sm" name="full_name" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Email</label>
          <input type="email" class="form-control form-control-sm" name="email" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Password</label>
          <input type="password" class="form-control form-control-sm" name="password" required>
        </div>
        <button class="btn btn-primary btn-sm w-100">Create Account</button>
        <p class="small text-muted mt-2 mb-0">Account requires admin approval before login.</p>
      </form>
    </div>
  </div>
</div>
<script>
document.getElementById('regForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await fetch('api/register.php', { method: 'POST', body: new FormData(e.target) });
  const j = await res.json();
  alert(j.ok ? 'Registered. Awaiting admin approval.' : (j.error || 'Failed'));
  if (j.ok) location.href = 'index.php?page=login';
});
</script>
