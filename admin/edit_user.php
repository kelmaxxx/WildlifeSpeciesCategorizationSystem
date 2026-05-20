<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$ROLES = ['admin', 'user'];

$id   = Mongo::oid($_GET['id'] ?? null);
$user = $id ? $db->findById('users', $id) : null;

if (!$user) {
    admin_layout_open('User not found', 'users');
    echo '<div class="alert error">User not found.</div>';
    echo '<a href="manage_users.php" class="btn btn-ghost" style="margin-top:16px">← Back</a>';
    admin_layout_close();
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    if ($username === '') {
        $error = 'Username is required.';
    } elseif ($password !== '' && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!in_array($role, $ROLES, true)) {
        $error = 'Invalid role.';
    } else {
        $clash = $db->findOne('users', ['username' => $username]);
        if ($clash && (string) $clash->_id !== (string) $id) {
            $error = 'Another user already has that username.';
        } else {
            $set = ['username' => $username, 'role' => $role];
            if ($password !== '') {
                $set['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $db->update('users', ['_id' => $id], $set);
            log_activity($db, 'update', 'user', $username);
            header('Location: manage_users.php');
            exit;
        }
    }
}

admin_layout_open('Edit User', 'users');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Community · editing
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Edit <i style="color:var(--oriole-deep)"><?= htmlspecialchars($user->username ?? 'user') ?>.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:16px;color:var(--ink-soft);margin:10px 0 0">
      Leave password blank to keep the existing one.
    </p>
  </div>
  <a href="manage_users.php" class="btn btn-ghost" style="align-self:flex-start">← Back</a>
</header>

<?php if ($error): ?>
  <div class="alert error" style="margin-bottom:24px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="contribute" style="max-width:560px">
  <?= csrf_field() ?>
  <fieldset class="fset">
    <legend>
      <span class="num">§ 01</span>
      <h2>Account</h2>
      <span class="req">Required</span>
    </legend>
    <div class="frow">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user->username ?? '') ?>">
    </div>
    <div class="frow">
      <label for="password">Password <span class="opt">Leave blank to keep current</span></label>
      <input type="password" id="password" name="password" autocomplete="new-password">
    </div>
    <div class="frow">
      <label for="role">Role</label>
      <select id="role" name="role" required>
        <?php foreach ($ROLES as $r): ?>
          <option value="<?= $r ?>"<?= ($user->role ?? 'user') === $r ? ' selected' : '' ?>><?= ucfirst($r) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </fieldset>
  <div class="submit-row">
    <a href="manage_users.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">Save changes <span class="arrow" aria-hidden="true"></span></button>
  </div>
</form>

<?php admin_layout_close(); ?>
