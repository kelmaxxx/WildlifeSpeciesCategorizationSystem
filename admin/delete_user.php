<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id   = Mongo::oid($_GET['id'] ?? $_POST['id'] ?? null);
$user = $id ? $db->findById('users', $id) : null;

if (!$user) {
    admin_layout_open('User not found', 'users');
    echo '<div class="alert error">User not found.</div>';
    echo '<a href="manage_users.php" class="btn btn-ghost" style="margin-top:16px">← Back</a>';
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

<div style="max-width:560px;margin:48px auto;padding:48px 40px;background:var(--cream);border:1px solid var(--rule-soft);border-radius:14px;text-align:center">
  <div style="font-family:var(--mono);font-size:11px;color:var(--berry);text-transform:uppercase;letter-spacing:.14em;margin-bottom:18px">
    § Destructive action
  </div>
  <h1 style="font-family:var(--serif);font-size:36px;line-height:1.1;margin:0 0 14px;color:var(--ink)">
    Delete this <i style="color:var(--berry)">contributor?</i>
  </h1>
  <p style="font-family:var(--serif);font-style:italic;font-size:17px;color:var(--ink-soft);margin:0 0 16px">
    You're about to delete <b style="color:var(--ink);font-style:normal"><?= htmlspecialchars($user->username) ?></b>
    (role: <?= htmlspecialchars($user->role ?? 'user') ?>).
  </p>

  <?php if ($isMe): ?>
    <div class="alert error" style="text-align:left;margin-bottom:24px">
      You can't delete the account you're currently signed in as. Switch to another admin first.
    </div>
  <?php endif; ?>

  <form method="POST" style="display:flex;justify-content:center;gap:12px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $id) ?>">
    <a href="manage_users.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" name="confirm" value="1"
            <?= $isMe ? 'disabled' : '' ?>
            style="font-family:var(--mono);font-size:13px;text-transform:uppercase;letter-spacing:.1em;padding:10px 20px;border:0;border-radius:6px;background:var(--berry);color:#fff;cursor:<?= $isMe ? 'not-allowed' : 'pointer' ?>;opacity:<?= $isMe ? '.5' : '1' ?>">
      Yes, delete
    </button>
  </form>
</div>

<?php admin_layout_close(); ?>
