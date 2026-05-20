<?php
/* partials/topbar.php — sticky public navigation
 * Expects $_SESSION['user'] (with ['name', 'role']) if logged in.
 */
$user = $_SESSION['user'] ?? null;
$current = basename($_SERVER['PHP_SELF']);
function nav_active(string $file, string $current): string {
    return $file === $current ? ' active' : '';
}
?>
<header class="bar">
  <div class="frame bar-inner">
    <a href="index.php" class="brand" aria-label="Wildlife Catalog home">
      <span class="mark" aria-hidden="true"></span>
      <span class="wordmark">Catalog</span>
      <span class="tag">Est. MMXXIV · Vol. III</span>
    </a>
    <nav class="primary">
      <a href="index.php"            class="<?= 'a' . nav_active('index.php', $current) ?>">Browse</a>
      <a href="habitats.php"         class="<?= 'a' . nav_active('habitats.php', $current) ?>">Habitats</a>
      <a href="submit_species.php"   class="<?= 'a' . nav_active('submit_species.php', $current) ?>">Submit</a>
      <?php if ($user): ?>
        <a href="my_submissions.php" class="<?= 'a' . nav_active('my_submissions.php', $current) ?>">My submissions</a>
        <?php if (($user['role'] ?? '') === 'admin'): ?>
          <a href="admin/dashboard.php">Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-cta">Sign out · <?= htmlspecialchars($user['name']) ?></a>
      <?php else: ?>
        <a href="login.php" class="nav-cta">Sign in</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
