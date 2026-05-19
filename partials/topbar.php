<?php
/**
 * Shared public topbar.
 *   $topbar_active — optional: 'submit' | 'submissions'. Hides that nav item
 *                    (so a user already on the submit page doesn't see a
 *                    "Submit species" button pointing back at the same page).
 *   $topbar_show_greeting — default false. Set to true on the home page.
 *   $topbar_browse_href   — default 'index.php'. The home page overrides this
 *                           to '#species' so Browse scrolls to the grid.
 */
$topbar_active        = $topbar_active        ?? '';
$topbar_show_greeting = $topbar_show_greeting ?? false;
$topbar_browse_href   = $topbar_browse_href   ?? 'index.php';
?>
<header class="topbar">
  <div class="brand">
    <span class="logo">&#127757;</span>
    Wildlife Explorer
  </div>
  <nav>
    <a class="btn ghost" href="<?= htmlspecialchars($topbar_browse_href) ?>">Browse</a>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <?php if ($topbar_show_greeting): ?>
        <span class="greeting">Hi, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></strong></span>
      <?php endif; ?>
      <?php if ($topbar_active !== 'submissions'): ?>
        <a class="btn ghost" href="my_submissions.php">My submissions</a>
      <?php endif; ?>
      <?php if ($topbar_active !== 'submit'): ?>
        <a class="btn" href="submit_species.php">Submit species</a>
      <?php endif; ?>
      <a class="btn ghost" href="logout.php">Logout</a>
    <?php else: ?>
      <a class="btn ghost" href="login.php">Sign in</a>
      <a class="btn ghost" href="register.php">Register</a>
      <a class="btn" href="admin/login.php">Admin</a>
    <?php endif; ?>
  </nav>
</header>
