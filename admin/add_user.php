<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$ROLES = ['admin', 'user'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!in_array($role, $ROLES, true)) {
        $error = 'Invalid role.';
    } elseif ($db->findOne('users', ['username' => $username])) {
        $error = 'A user with that username already exists.';
    } else {
        $db->insert('users', [
            'username'   => $username,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'role'       => $role,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
        ]);
        log_activity($db, 'create', 'user', $username);
        header('Location: manage_users.php');
        exit;
    }
}

admin_layout_open('Add User', 'users');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Community · new member
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Add <i style="color:var(--oriole-deep)">contributor.</i>
    </h1>
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
      <input type="text" id="username" name="username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="member_handle">
    </div>
    <div class="frow">
      <label for="password">Password <span class="opt">≥ 6 chars</span></label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="frow">
      <label for="role">Role</label>
      <select id="role" name="role" required>
        <?php foreach ($ROLES as $r): ?>
          <option value="<?= $r ?>"<?= ($_POST['role'] ?? 'user') === $r ? ' selected' : '' ?>>
            <?= ucfirst($r) ?> — <?= [
              'admin' => 'full catalog access',
              'user'  => 'browse + submit species',
            ][$r] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </fieldset>
  <div class="submit-row">
    <a href="manage_users.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">Add contributor <span class="arrow" aria-hidden="true"></span></button>
  </div>
</form>

<?php admin_layout_close(); ?>
