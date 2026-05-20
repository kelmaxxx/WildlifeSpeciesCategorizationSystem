<?php
require_once __DIR__ . '/auth.php';

$pending = $db->find(
    'species',
    ['approval_status' => 'pending'],
    ['sort' => ['created_at' => -1]]
);

admin_layout_open('Pending Approvals', 'approvals');
?>

<header class="page-header">
  <div>
    <h1>Pending approvals</h1>
    <p class="subtitle">Review and approve (or reject) species submitted by users.</p>
  </div>
  <a href="manage_species.php" class="btn ghost">All species &rarr;</a>
</header>

<div class="panel">
  <table class="table">
    <thead>
      <tr>
        <th>Submitted species</th>
        <th>Category</th>
        <th>Habitat</th>
        <th>Submitted by</th>
        <th>Submitted</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($pending) === 0): ?>
        <tr><td colspan="6" class="table-empty">Nothing pending — you're all caught up. &#127881;</td></tr>
      <?php else: foreach ($pending as $s):
        $sid = (string) $s->_id;
        $uploader = isset($s->uploader_id) ? $db->findById('users', $s->uploader_id) : null;
        $submitted = ($s->created_at ?? null) instanceof MongoDB\BSON\UTCDateTime
                   ? $s->created_at->toDateTime()->format('M j, Y') : '—';
      ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($s->name ?? '') ?></strong>
            <br><em style="color:var(--slate-500);font-size:.8rem"><?= htmlspecialchars($s->scientific_name ?? '') ?></em>
          </td>
          <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
          <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
          <td><?= $uploader ? htmlspecialchars($uploader->username) : '—' ?></td>
          <td><?= htmlspecialchars($submitted) ?></td>
          <td>
            <div class="actions">
              <a class="view" href="edit_species.php?id=<?= urlencode($sid) ?>">View</a>
              <form method="POST" action="approval_action.php" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($sid) ?>">
                <input type="hidden" name="decision" value="approve">
                <button type="submit" class="approve">Approve</button>
              </form>
              <form method="POST" action="approval_action.php" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($sid) ?>">
                <input type="hidden" name="decision" value="reject">
                <button type="submit" class="reject">Reject</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php admin_layout_close();
