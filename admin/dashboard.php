<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$totalSpecies     = $db->count('species');
$approvedSpecies  = $db->count('species', ['approval_status' => 'approved']);
$endangeredCount  = $db->count('species', ['approval_status' => 'approved', 'is_endangered' => true]);
$totalCategories  = $db->count('categories');
$totalHabitats    = $db->count('habitats');
$pendingCount     = $db->count('species', ['approval_status' => 'pending']);

$pending  = $db->find('species', ['approval_status' => 'pending'], ['sort' => ['_id' => -1], 'limit' => 5]);
$recent   = $db->find('species', ['approval_status' => 'approved'], ['sort' => ['_id' => -1], 'limit' => 6]);
$activity = $db->find('activity_log', [], ['sort' => ['created_at' => -1], 'limit' => 8]);

function plate_num($id): string {
    return strtoupper(substr((string) $id, -3));
}

admin_layout_open('Dashboard', 'dashboard');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Editor's desk · <?= date('l, j F Y') ?>
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Welcome back, <i style="color:var(--oriole-deep)"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?></i>.
    </h1>
  </div>
</header>

<div class="stats">
  <div class="stat">
    <div class="num"><?= number_format($totalSpecies) ?></div>
    <div class="label">Total species</div>
    <div class="delta"><?= number_format($approvedSpecies) ?> published</div>
  </div>
  <div class="stat">
    <div class="num"><?= number_format($endangeredCount) ?></div>
    <div class="label">Endangered</div>
    <div class="delta" style="color:var(--status-end)">Listed at risk</div>
  </div>
  <div class="stat">
    <div class="num"><?= number_format($pendingCount) ?></div>
    <div class="label">Pending approval</div>
    <div class="delta" style="color:var(--status-vuln)">Awaiting your review</div>
  </div>
  <div class="stat">
    <div class="num"><?= number_format($totalHabitats) ?></div>
    <div class="label">Habitats</div>
    <div class="delta"><?= number_format($totalCategories) ?> categories</div>
  </div>
</div>

<section class="panel" style="border-right:0;padding:32px 0">
  <div class="panel-head">
    <h2 style="font-family:var(--serif);font-size:28px;letter-spacing:-.01em;margin:0;color:var(--ink)">Pending approvals.</h2>
    <div class="tools">
      <span class="count"><?= $pendingCount ?> waiting</span>
      <a href="manage_approvals.php">Review all →</a>
    </div>
  </div>

  <?php if (count($pending) === 0): ?>
    <div style="padding:32px 0;font-family:var(--serif);font-style:italic;color:var(--ink-soft)">
      Inbox zero. Nothing waiting for your review.
    </div>
  <?php else: ?>
    <table class="tbl">
      <thead>
        <tr>
          <th class="num">Plate</th>
          <th>Specimen</th>
          <th>Diet</th>
          <th>Habitat</th>
          <th>Submitted</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pending as $s):
          $sid    = (string) $s->_id;
          $plate  = plate_num($s->_id);
          $when   = ($s->created_at ?? null) instanceof MongoDB\BSON\UTCDateTime
                  ? $s->created_at->toDateTime()->format('j M') : '—';
          $img    = $s->image_url ?? '';
        ?>
          <tr>
            <td class="num"><span class="plate">№<?= $plate ?></span></td>
            <td>
              <div class="spec">
                <div class="thumb">
                  <?php if ($img): ?>
                    <div class="img" style="background-image:url('<?= htmlspecialchars($img) ?>')"></div>
                  <?php endif; ?>
                </div>
                <div class="name">
                  <a class="common" href="../species_detail.php?id=<?= urlencode($sid) ?>" target="_blank" style="color:inherit;text-decoration:none">
                    <?= htmlspecialchars($s->name ?? 'Unknown') ?>
                  </a>
                  <div class="latin"><?= htmlspecialchars($s->scientific_name ?? '') ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
            <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
            <td class="when"><?= htmlspecialchars($when) ?></td>
            <td><span class="status" data-s="pending">Pending</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<section class="panel" style="border-right:0;padding:32px 0;border-top:1px solid var(--rule-soft)">
  <div class="panel-head">
    <h2 style="font-family:var(--serif);font-size:28px;letter-spacing:-.01em;margin:0;color:var(--ink)">Recently published.</h2>
    <div class="tools">
      <a href="manage_species.php">Manage all species →</a>
    </div>
  </div>

  <?php if (count($recent) === 0): ?>
    <div style="padding:32px 0;font-family:var(--serif);font-style:italic;color:var(--ink-soft)">
      No species published yet — <a href="add_species.php" style="color:var(--ink)">add the first one</a>.
    </div>
  <?php else: ?>
    <table class="tbl">
      <thead>
        <tr>
          <th class="num">Plate</th>
          <th>Specimen</th>
          <th>Diet</th>
          <th>Habitat</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $s):
          $sid    = (string) $s->_id;
          $plate  = plate_num($s->_id);
          $img    = $s->image_url ?? '';
          $isEnd  = !empty($s->is_endangered);
        ?>
          <tr>
            <td class="num"><span class="plate">№<?= $plate ?></span></td>
            <td>
              <div class="spec">
                <div class="thumb">
                  <?php if ($img): ?>
                    <div class="img" style="background-image:url('<?= htmlspecialchars($img) ?>')"></div>
                  <?php endif; ?>
                </div>
                <div class="name">
                  <a class="common" href="edit_species.php?id=<?= urlencode($sid) ?>" style="color:inherit;text-decoration:none">
                    <?= htmlspecialchars($s->name ?? 'Unknown') ?>
                  </a>
                  <div class="latin"><?= htmlspecialchars($s->scientific_name ?? '') ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
            <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
            <td><span class="status" data-s="approved"><?= $isEnd ? 'Endangered' : 'Approved' ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php admin_layout_close(); ?>
