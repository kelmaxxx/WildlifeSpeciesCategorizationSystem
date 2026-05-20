<?php
require_once __DIR__ . '/auth.php';

$users = $db->find('users', [], ['sort' => ['username' => 1]]);

admin_layout_open('Manage Users', 'users');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Community · <?= count($users) ?> members
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      <i style="color:var(--oriole-deep)">Contributors.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:17px;color:var(--ink-soft);margin:12px 0 0;max-width:620px">
      Editors manage the catalog. Members can submit species for review.
    </p>
  </div>
  <a href="add_user.php" class="btn btn-primary" style="align-self:flex-start">
    Add user <span class="arrow" aria-hidden="true"></span>
  </a>
</header>

<section class="panel" style="border-right:0;padding:32px 0">
  <div class="panel-head">
    <h2 style="font-family:var(--serif);font-size:28px;letter-spacing:-.01em;margin:0;color:var(--ink)">Records.</h2>
  </div>

  <?php if (count($users) === 0): ?>
    <div style="padding:48px 0;text-align:center;font-family:var(--serif);font-style:italic;color:var(--ink-soft)">
      No users yet.
    </div>
  <?php else: ?>
    <table class="tbl">
      <thead>
        <tr>
          <th>Member</th>
          <th>Role</th>
          <th>Joined</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          $uid     = (string) $u->_id;
          $role    = $u->role ?? 'user';
          $username = $u->username ?? '';
          $initial  = strtoupper(substr($username, 0, 1));
          $created = isset($u->created_at) && $u->created_at instanceof MongoDB\BSON\UTCDateTime
                   ? $u->created_at->toDateTime()->format('j M Y') : '—';
          $isMe    = $uid === ($_SESSION['admin_id'] ?? '');
        ?>
          <tr>
            <td>
              <div class="spec">
                <div class="thumb" style="background:var(--cream-deep);display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:18px;color:var(--ink)">
                  <?= htmlspecialchars($initial ?: '?') ?>
                </div>
                <div class="name">
                  <span class="common"><?= htmlspecialchars($username) ?><?php if ($isMe): ?> <span style="font-family:var(--mono);font-size:10px;color:var(--oriole-deep);text-transform:uppercase;letter-spacing:.1em">· you</span><?php endif; ?></span>
                  <div class="latin">User №<?= strtoupper(substr($uid, -3)) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="status" data-s="<?= $role === 'admin' ? 'approved' : 'pending' ?>"><?= htmlspecialchars(ucfirst($role)) ?></span>
            </td>
            <td class="when"><?= htmlspecialchars($created) ?></td>
            <td style="text-align:right">
              <div style="display:inline-flex;gap:6px">
                <a href="edit_user.php?id=<?= urlencode($uid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--rule-soft);border-radius:6px;text-decoration:none">Edit</a>
                <?php if (!$isMe): ?>
                  <a href="delete_user.php?id=<?= urlencode($uid) ?>"
                     style="font-family:var(--mono);font-size:11px;color:var(--berry);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--berry);border-radius:6px;text-decoration:none">Delete</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php admin_layout_close(); ?>
