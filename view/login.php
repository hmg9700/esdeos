<?php $csrf = ensure_csrf_token(); ?>
<div class="row justify-content-center">
  <div class="col-12 col-sm-10 col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3"><i class="fa-solid fa-right-to-bracket me-2"></i> Login</h1>
        <?php if (!empty($_GET['error'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['msg'])): ?>
          <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>
        <form method="post" action="api/login.php" class="row g-3">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required placeholder="admin@example.com">
          </div>
          <div class="col-12">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required placeholder="admin123">
          </div>
          <div class="col-12 d-grid">
            <button class="btn btn-primary">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Sign in
            </button>
          </div>
          <div class="col-12">
            <div class="text-muted small">
              First run seeded user: admin@example.com / admin123. Change after login.
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
