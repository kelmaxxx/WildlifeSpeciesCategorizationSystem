<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$totalSpecies    = $db->count('species');
$endangered      = $db->count('species', ['is_endangered' => true]);
$totalCategories = $db->count('categories');
$totalHabitats   = $db->count('habitats');
$pending         = $db->count('species', ['approval_status' => 'pending']);

$recent   = $db->find('species', [], ['sort' => ['_id' => -1], 'limit' => 5]);
$activity = $db->find('activity_log', [], ['sort' => ['created_at' => -1], 'limit' => 8]);

admin_layout_open('Dashboard', 'dashboard');
?>

<header class="page-header">
  <div>
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?></h1>
    <p class="subtitle">Here's a snapshot of your wildlife catalog.</p>
  </div>
</header>

<div class="cards-stat">
  <div class="stat-card green">
    <div class="label">Total Species</div>
    <div class="value"><?= $totalSpecies ?></div>
  </div>
  <div class="stat-card violet">
    <div class="label">Endangered</div>
    <div class="value"><?= $endangered ?></div>
  </div>
  <div class="stat-card amber">
    <div class="label">Categories</div>
    <div class="value"><?= $totalCategories ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Habitats</div>
    <div class="value"><?= $totalHabitats ?></div>
  </div>
  <div class="stat-card rose">
    <div class="label">Pending Approvals</div>
    <div class="value"><?= $pending ?></div>
  </div>
</div>

<div class="panel-grid">
  <div class="panel">
    <div class="panel-header">
      <h2>Recently added species</h2>
      <a href="manage_species.php" class="btn ghost">Manage species &rarr;</a>
    </div>
    <table class="table">
      <thead>
        <tr><th>Name</th><th>Category</th><th>Habitat</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (count($recent) === 0): ?>
          <tr><td colspan="4" class="table-empty">No species yet — <a href="add_species.php">add one</a>.</td></tr>
        <?php else: foreach ($recent as $s):
          $status = $s->approval_status ?? 'approved';
        ?>
          <tr>
            <td><strong><?= htmlspecialchars($s->name ?? '') ?></strong>
                <br><em style="color:var(--slate-500);font-size:.8rem"><?= htmlspecialchars($s->scientific_name ?? '') ?></em></td>
            <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
            <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
            <td><span class="badge status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="panel">
    <div class="panel-header">
      <h2>Recent activity</h2>
    </div>
    <ul class="activity-list">
      <?php if (count($activity) === 0): ?>
        <li class="empty" style="display:block">No admin actions yet.</li>
      <?php else: foreach ($activity as $a):
        $action = $a->action ?? 'create';
      ?>
        <li>
          <span class="icon <?= htmlspecialchars(activity_icon_class($action)) ?>">
            <?= htmlspecialchars(activity_icon_glyph($action)) ?>
          </span>
          <div>
            <strong><?= htmlspecialchars($a->actor_username ?? 'system') ?></strong>
            <?= htmlspecialchars(activity_verb($action)) ?>
            <?= htmlspecialchars($a->target_type ?? '') ?>
            <strong><?= htmlspecialchars($a->target_name ?? '') ?></strong>
            <div class="meta"><?= htmlspecialchars(format_when($a->created_at ?? null)) ?></div>
          </div>
        </li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
</div>

<?php admin_layout_close();
