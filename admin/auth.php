<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../mongo.php';
require_once __DIR__ . '/../lib/csrf.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

/**
 * Render the admin chrome (sidebar + main wrapper opening tag).
 * Each admin page calls admin_layout_open('Page Title', 'active-key')
 * and admin_layout_close().
 */
function admin_layout_open(string $title, string $active = ''): void
{
    global $db;
    $pendingCount    = $db ? $db->count('species', ['approval_status' => 'pending']) : 0;
    $speciesCount    = $db ? $db->count('species') : 0;
    $categoriesCount = $db ? $db->count('categories') : 0;
    $habitatsCount   = $db ? $db->count('habitats') : 0;
    $usersCount      = $db ? $db->count('users') : 0;

    $adminUser   = $_SESSION['admin_username'] ?? 'Admin';
    $avatar      = strtoupper(substr($adminUser, 0, 1));
    $pageTitle   = $title . ' — Wildlife Catalog · Admin';

    $nav = [
        'editorial' => [
            'label' => 'Editorial',
            'items' => [
                ['id' => 'dashboard', 'href' => 'dashboard.php',        'glyph' => 'D', 'label' => 'Dashboard'],
                ['id' => 'approvals', 'href' => 'manage_approvals.php', 'glyph' => 'A', 'label' => 'Approvals', 'badge' => $pendingCount, 'badge_warn' => $pendingCount > 0],
                ['id' => 'species',   'href' => 'manage_species.php',   'glyph' => 'S', 'label' => 'All species', 'badge' => $speciesCount],
            ],
        ],
        'taxonomy' => [
            'label' => 'Taxonomy',
            'items' => [
                ['id' => 'categories', 'href' => 'manage_categories.php', 'glyph' => 'C', 'label' => 'Categories', 'badge' => $categoriesCount],
                ['id' => 'habitats',   'href' => 'manage_habitats.php',   'glyph' => 'H', 'label' => 'Habitats',   'badge' => $habitatsCount],
            ],
        ],
        'community' => [
            'label' => 'Community',
            'items' => [
                ['id' => 'users', 'href' => 'manage_users.php', 'glyph' => 'U', 'label' => 'Contributors', 'badge' => $usersCount],
            ],
        ],
    ];

    $bottom_nav = [
        ['id' => 'profile', 'href' => 'profile.php', 'glyph' => 'P', 'label' => 'Profile'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body data-theme="cream">

<div class="admin">
  <aside class="sidebar">
    <a href="dashboard.php" class="sb-brand">
      <span class="mark" aria-hidden="true"></span>
      <span class="wordmark">Catalog</span>
      <span class="role">Admin</span>
    </a>

    <?php foreach ($nav as $group): ?>
      <div class="sb-section">
        <div class="label"><?= htmlspecialchars($group['label']) ?></div>
        <?php foreach ($group['items'] as $item): ?>
          <a class="sb-nav-item"
             <?= $active === $item['id'] ? 'aria-current="page"' : '' ?>
             href="<?= htmlspecialchars($item['href']) ?>">
            <span class="glyph"><?= htmlspecialchars($item['glyph']) ?></span>
            <span class="label-txt"><?= htmlspecialchars($item['label']) ?></span>
            <?php if (isset($item['badge'])): ?>
              <span class="badge-mini<?= !empty($item['badge_warn']) ? ' warn' : '' ?>">
                <?= number_format((int) $item['badge']) ?>
              </span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <div class="sb-spacer"></div>

    <div class="sb-section">
      <div class="label">You</div>
      <?php foreach ($bottom_nav as $item): ?>
        <a class="sb-nav-item"
           <?= $active === $item['id'] ? 'aria-current="page"' : '' ?>
           href="<?= htmlspecialchars($item['href']) ?>">
          <span class="glyph"><?= htmlspecialchars($item['glyph']) ?></span>
          <span class="label-txt"><?= htmlspecialchars($item['label']) ?></span>
        </a>
      <?php endforeach; ?>
      <a class="sb-nav-item" href="../index.php" target="_blank">
        <span class="glyph">V</span>
        <span class="label-txt">View public site</span>
      </a>
    </div>

    <div class="sb-user">
      <span class="av" aria-hidden="true"><?= htmlspecialchars($avatar) ?></span>
      <div>
        <div class="who"><?= htmlspecialchars($adminUser) ?></div>
        <div class="role-line">Editor</div>
      </div>
      <a href="../logout.php" class="signout" title="Sign out">↗</a>
    </div>
  </aside>

  <section class="main">
<?php
}

function admin_layout_close(): void
{
    ?>
  </section>
</div>

</body>
</html>
<?php
}
