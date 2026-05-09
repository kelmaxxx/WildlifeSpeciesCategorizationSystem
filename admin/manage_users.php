<?php
require_once __DIR__ . '/auth.php';

$users = $db->find('users', [], ['sort' => ['username' => 1]]);

admin_layout_open('Manage Users', 'users');
?>

<header class="page-header">
  <div>
    <h1>Users</h1>
    <p class="subtitle">Admins manage the catalog. Uploaders and users can submit species for review.</p>
  </div>
  <a href="add_user.php" class="btn">&#43; Add new user</a>
</header>

<div class="panel">
  <table class="table">
    <thead>
      <tr><th>Username</th><th>Role</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (count($users) === 0): ?>
        <tr><td colspan="4" class="table-empty">No users yet.</td></tr>
      <?php else: foreach ($users as $u):
        $uid     = (string) $u->_id;
        $role    = $u->role ?? 'user';
        $created = isset($u->created_at) && $u->created_at instanceof MongoDB\BSON\UTCDateTime
                 ? $u->created_at->toDateTime()->format('M j, Y') : '—';
        $isMe    = $uid === ($_SESSION['admin_id'] ?? '');
      ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($u->username ?? '') ?></strong>
            <?php if ($isMe): ?><span class="badge" style="margin-left:.5rem">you</span><?php endif; ?>
          </td>
          <td><span class="badge role-<?= htmlspecialchars($role) ?>"><?= htmlspecialchars(ucfirst($role)) ?></span></td>
          <td><?= htmlspecialchars($created) ?></td>
          <td>
            <div class="actions">
              <a class="edit" href="edit_user.php?id=<?= urlencode($uid) ?>">Edit</a>
              <?php if (!$isMe): ?>
                <a class="del"  href="delete_user.php?id=<?= urlencode($uid) ?>">Delete</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php admin_layout_close();
