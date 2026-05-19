<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id   = Mongo::oid($_GET['id'] ?? $_POST['id'] ?? null);
$user = $id ? $db->findById('users', $id) : null;

if (!$user) {
    admin_layout_open('User not found', 'users');
    echo '<div class="alert error">User not found.</div>';
    echo '<a href="manage_users.php" class="btn">&larr; Back</a>';
    admin_layout_close();
    exit;
}

$isMe = (string) $id === ($_SESSION['admin_id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && !$isMe) {
    csrf_check();
    $name = $user->username;
    $db->delete('users', ['_id' => $id]);
    log_activity($db, 'delete', 'user', $name);
    header('Location: manage_users.php');
    exit;
}

admin_layout_open('Delete User', 'users');
?>
<div class="confirm-card">
  <div class="icon">&#9888;</div>
  <h2>Delete this user?</h2>
  <p>You're about to delete <strong><?= htmlspecialchars($user->username) ?></strong>
     (role: <?= htmlspecialchars($user->role ?? 'user') ?>).</p>

  <?php if ($isMe): ?>
    <div class="alert error" style="text-align:left">
      You can't delete the account you're currently logged in as. Switch to another admin first.
    </div>
  <?php endif; ?>

  <form method="POST" class="confirm-actions">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id) ?>">
    <a href="manage_users.php" class="btn ghost">Cancel</a>
    <button type="submit" name="confirm" value="1" class="btn danger" <?= $isMe ? 'disabled style="opacity:.6;cursor:not-allowed"' : '' ?>>
      Yes, delete
    </button>
  </form>
</div>
<?php admin_layout_close();
