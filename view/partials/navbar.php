<?php $user = current_user(); $csrf = ensure_csrf_token(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="index.php?page=dashboard">
      <i class="fa-solid fa-bolt"></i> ESD-EOS
    </a>
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#mainNav"
      aria-controls="mainNav"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? ' active' : '' ?>" href="index.php?page=dashboard">
            <i class="fa-solid fa-chart-simple me-1"></i> Dashboard
          </a>
        </li>
        <!-- Menu ESD Checker -->
        <li class="nav-item dropdown">
          <?php $p = $_GET['page'] ?? 'esd_checker_summary'; $isESD = in_array($p, ['esd_checker_summary', 'esd_checker_log'], true); ?>
          <a class="nav-link dropdown-toggle<?= $isESD ? ' active' : '' ?>" href="#" id="esdCheckerMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-microchip me-1"></i> ESD Checker
          </a>
          <ul class="dropdown-menu" aria-labelledby="esdCheckerMenu">
            <li><a class="dropdown-item<?= $p === 'esd_checker_summary' ? ' active' : '' ?>" href="index.php?page=esd_checker_summary">Summary</a></li>
            <li><a class="dropdown-item<?= $p === 'esd_checker_log' ? ' active' : '' ?>" href="index.php?page=esd_checker_log">Log History</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <?php $p = $_GET['page'] ?? ''; $isAudit = in_array($p, ['manage-checksheet','checksheet','summary-audit'], true); ?>
          <a class="nav-link dropdown-toggle<?= $isAudit ? ' active' : '' ?>" href="#" id="checksheetMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-list-check me-1"></i> Audit
          </a>
          <ul class="dropdown-menu" aria-labelledby="checksheetMenu">
            <li><a class="dropdown-item<?= $p==='audit-summary'?' active':'' ?>" href="index.php?page=audit-summary">Result Audit</a></li>
            <li><a class="dropdown-item<?= $p==='checksheet'?' active':'' ?>" href="index.php?page=checksheet">Checksheet Audit</a></li>
            <li><hr class="dropdown-divider"></li> 
            <li><a class="dropdown-item<?= $p==='manage-checksheet'?' active':'' ?>" href="index.php?page=manage-checksheet">Manage Check Sheet</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= ($_GET['page'] ?? '') === 'activity' ? ' active' : '' ?>" href="index.php?page=activity">
            <i class="fa-solid fa-calendar-days me-1"></i> Calendar
          </a>
        </li>        
       <?php if ($user && ($user['level'] ?? '') === 'admin'): ?>
        <?php
          $p = $_GET['page'] ?? '';
          $isAdminDropdown = in_array($p, ['master', 'manage-users'], true);
        ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $isAdminDropdown ? ' active' : '' ?>" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-user-shield me-1"></i> Admin Panel
          </a>
          <ul class="dropdown-menu" aria-labelledby="adminMenu">
            <li>
              <a class="dropdown-item<?= $p === 'master' ? ' active' : '' ?>" href="index.php?page=master">
                <i class="fa-solid fa-database me-1"></i> Data Master
              </a>
            </li>
            <li><hr class="dropdown-divider"></li> 
            <li>
              <a class="dropdown-item<?= $p === 'manage-users' ? ' active' : '' ?>" href="index.php?page=manage-users">
                <i class="fa-solid fa-users-gear me-1"></i> Manage Users
              </a>
            </li>
          </ul>
        </li>
      <?php endif; ?>

        <?php if ($user): ?>
          <li class="nav-item">
            <a class="nav-link<?= ($_GET['page'] ?? '') === 'my-account' ? ' active' : '' ?>" href="index.php?page=my-account">
              <i class="fa-regular fa-id-badge me-1"></i> My Account
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if ($user): ?>
          <li class="nav-item">
            <span class="navbar-text me-3 text-light">
              <i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($user['username']) ?>
            </span>
          </li>
          <li class="nav-item">
            <form class="d-inline" method="post" action="api/logout.php">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <button class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
              </button>
            </form>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-light btn-sm me-2" href="index.php?page=register">
              <i class="fa-regular fa-user me-1"></i> Register
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-light btn-sm" href="index.php?page=login">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Login
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
