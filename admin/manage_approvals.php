<?php
require_once __DIR__ . '/auth.php';

$pending = $db->find(
    'species',
    ['approval_status' => 'pending'],
    ['sort' => ['created_at' => -1]]
);

function plate_num($id): string {
    return strtoupper(substr((string) $id, -3));
}

admin_layout_open('Pending Approvals', 'approvals');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Editorial queue · awaiting review
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Pending <i style="color:var(--oriole-deep)">approvals.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:17px;color:var(--ink-soft);margin:12px 0 0;max-width:620px">
      Each entry below was submitted by a contributor and is waiting on your decision to publish, hold, or reject.
    </p>
  </div>
  <a href="manage_species.php" class="btn btn-ghost" style="align-self:flex-start">All species →</a>
</header>

<section class="panel" style="border-right:0;padding:32px 0">
  <div class="panel-head">
    <h2 style="font-family:var(--serif);font-size:28px;letter-spacing:-.01em;margin:0;color:var(--ink)">Queue.</h2>
    <div class="tools">
      <span class="count"><?= count($pending) ?> waiting</span>
    </div>
  </div>

  <?php if (count($pending) === 0): ?>
    <div style="padding:48px 0;text-align:center">
      <div style="font-family:var(--serif);font-size:22px;font-style:italic;color:var(--ink-soft);margin-bottom:8px">
        Inbox zero.
      </div>
      <div style="font-family:var(--mono);font-size:12px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em">
        Nothing pending — you're all caught up.
      </div>
    </div>
  <?php else: ?>
    <table class="tbl">
      <thead>
        <tr>
          <th class="num">Plate</th>
          <th>Specimen</th>
          <th>Diet</th>
          <th>Habitat</th>
          <th>Contributor</th>
          <th>Submitted</th>
          <th style="text-align:right">Decision</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pending as $s):
          $sid = (string) $s->_id;
          $plate = plate_num($s->_id);
          $uploader = isset($s->uploader_id) ? $db->findById('users', $s->uploader_id) : null;
          $submitted = ($s->created_at ?? null) instanceof MongoDB\BSON\UTCDateTime
                     ? $s->created_at->toDateTime()->format('j M Y') : '—';
          $img = $s->image_url ?? '';
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
            <td class="who"><?= $uploader ? '<b>' . htmlspecialchars($uploader->username) . '</b>' : '—' ?></td>
            <td class="when"><?= htmlspecialchars($submitted) ?></td>
            <td style="text-align:right">
              <div style="display:inline-flex;gap:8px;align-items:center">
                <a href="edit_species.php?id=<?= urlencode($sid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--rule-soft);border-radius:6px;text-decoration:none">
                  View
                </a>
                <form method="POST" action="approval_action.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= htmlspecialchars($sid) ?>">
                  <input type="hidden" name="decision" value="approve">
                  <button type="submit"
                          style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.1em;padding:6px 12px;border:0;border-radius:6px;background:var(--ink);color:var(--paper);cursor:pointer">
                    Approve
                  </button>
                </form>
                <form method="POST" action="approval_action.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= htmlspecialchars($sid) ?>">
                  <input type="hidden" name="decision" value="reject">
                  <button type="submit"
                          style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.1em;padding:6px 12px;border:1px solid var(--berry);border-radius:6px;background:transparent;color:var(--berry);cursor:pointer">
                    Reject
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php admin_layout_close(); ?>
