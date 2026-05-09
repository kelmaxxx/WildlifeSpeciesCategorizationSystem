<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../mongo.php';

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
    $pendingCount = $db ? $db->count('species', ['approval_status' => 'pending']) : 0;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $css=['admin','auth']; $base='../'; include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>
<div class="admin-body">
  <aside class="admin-sidebar">
    <div class="brand">
      <span class="logo">&#127757;</span>
      Wildlife Admin
    </div>

    <a href="dashboard.php"        class="<?= $active === 'dashboard'  ? 'active' : '' ?>">&#128202; Dashboard</a>

    <div class="group-label">Catalog</div>
    <a href="manage_species.php"   class="<?= $active === 'species'    ? 'active' : '' ?>">&#128062; Species</a>
    <a href="manage_categories.php"class="<?= $active === 'categories' ? 'active' : '' ?>">&#127828; Categories</a>
    <a href="manage_habitats.php"  class="<?= $active === 'habitats'   ? 'active' : '' ?>">&#127795; Habitats</a>
    <a href="manage_approvals.php" class="<?= $active === 'approvals'  ? 'active' : '' ?>">
      &#9989; Approvals
      <?php if ($pendingCount > 0): ?>
        <span class="badge-count"><?= $pendingCount ?></span>
      <?php endif; ?>
    </a>

    <div class="group-label">People</div>
    <a href="manage_users.php"     class="<?= $active === 'users'      ? 'active' : '' ?>">&#128101; Users</a>
    <a href="profile.php"          class="<?= $active === 'profile'    ? 'active' : '' ?>">&#128100; My Profile</a>

    <div class="group-label">Site</div>
    <a href="../index.php" target="_blank">&#128279; View public site</a>

    <a href="logout.php" class="logout">&#128274; Logout</a>
  </aside>

  <main class="admin-main">
    <?php
}

function admin_layout_close(): void
{
    ?>
  </main>
</div>
</body>
</html>
    <?php
}
