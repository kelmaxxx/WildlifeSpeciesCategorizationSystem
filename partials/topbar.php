<?php
/* partials/topbar.php — sticky public navigation
 * Uses the project's existing flat session keys: user_id / user_name / user_role.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
$loggedIn = !empty($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
$current  = basename($_SERVER['PHP_SELF']);
function nav_active(string $file, string $current): string {
    return $file === $current ? ' active' : '';
}
?>
<header class="bar">
  <div class="frame bar-inner">
    <a href="index.php" class="brand" aria-label="Wildlife Species Categorization System — home">
      <span class="mark" aria-hidden="true"></span>
      <span class="brand-text">
        <span class="wordmark">Wildlife Species Categorization System</span>
        <span class="wordmark-short">WSCS</span>
      </span>
    </a>
    <nav class="primary">
      <a href="index.php" class="a<?= nav_active('index.php', $current) ?>">Browse</a>
      <a href="submit_species.php" class="a<?= nav_active('submit_species.php', $current) ?>">Submit</a>
      <?php if ($loggedIn): ?>
        <a href="my_submissions.php" class="a<?= nav_active('my_submissions.php', $current) ?>">My submissions</a>
        <?php if ($userRole === 'admin'): ?>
          <a href="admin/dashboard.php" class="a">Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-cta">Sign out · <?= htmlspecialchars($userName) ?></a>
      <?php else: ?>
        <a href="login.php" class="nav-cta">Sign in</a>
      <?php endif; ?>
      <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Toggle light or forest theme" title="Toggle theme">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>
        </svg>
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="4"/>
          <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
        </svg>
      </button>
    </nav>
  </div>
</header>
<script>
  (function () {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var body = document.body;
      var next = (body.getAttribute('data-theme') === 'forest') ? 'light' : 'forest';
      body.setAttribute('data-theme', next);
      try { localStorage.setItem('wscs-theme', next); } catch (e) {}
    });
  })();
</script>
