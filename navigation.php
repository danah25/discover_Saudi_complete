<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    http_response_code(403);
    exit;
}

$navigationPage = basename($_SERVER['SCRIPT_NAME']);
$navigationRole = isset($_SESSION['user_id']) ? ($_SESSION['role'] ?? '') : '';
?>
<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="index.html">
      <img src="assets/palm.svg" alt="" width="44" height="54">
      <span>اكتشف السعودية<small>وجهات المملكة</small></span>
    </a>
    <button class="menu-toggle js-only" type="button" aria-expanded="false" aria-controls="main-navigation">القائمة <span aria-hidden="true">☰</span></button>
    <nav id="main-navigation" class="main-navigation" aria-label="القائمة الرئيسية">
      <ul>
        <li><a href="index.html"<?= in_array($navigationPage, ['index.php', 'index.html'], true) ? ' class="current" aria-current="page"' : '' ?>>الرئيسية</a></li>
        <li><a href="regions.php"<?= $navigationPage === 'regions.php' ? ' class="current" aria-current="page"' : ($navigationPage === 'destination.php' ? ' class="current"' : '') ?>>الوجهات</a></li>
        <?php if (in_array($navigationRole, ['user', 'admin'], true)) { ?>
          <?php if ($navigationRole === 'admin') { ?>
            <li><a href="admin.php" lang="en"<?= $navigationPage === 'admin.php' ? ' class="current" aria-current="page"' : '' ?>>Admin Panel</a></li>
          <?php } else { ?>
            <li><a href="dashboard.php" lang="en"<?= $navigationPage === 'dashboard.php' ? ' class="current" aria-current="page"' : '' ?>>Dashboard</a></li>
          <?php } ?>
          <li>
            <form action="logout.php" method="post">
              <button class="nav-logout" type="submit" lang="en">Logout</button>
            </form>
          </li>
        <?php } else { ?>
          <li>
            <details class="account-menu">
              <summary lang="en">Account</summary>
              <div class="account-links" lang="en" dir="ltr">
                <a href="login.php"<?= $navigationPage === 'login.php' ? ' class="current" aria-current="page"' : '' ?>>Login</a>
                <a href="register.php"<?= $navigationPage === 'register.php' ? ' class="current" aria-current="page"' : '' ?>>Create Account</a>
              </div>
            </details>
          </li>
        <?php } ?>
      </ul>
    </nav>
  </div>
</header>
