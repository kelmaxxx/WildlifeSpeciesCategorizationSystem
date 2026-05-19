<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$ROLES = ['admin', 'uploader', 'user'];

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

<header class="page-header">
  <div>
    <h1>Add new user</h1>
    <p class="subtitle">Create a login for an admin, uploader, or regular user.</p>
  </div>
  <a href="manage_users.php" class="btn ghost">&larr; Back</a>
</header>

<div class="form-card">
  <?php if (!empty($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-row">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="form-row">
      <label for="password">Password <span class="hint">at least 6 characters</span></label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="form-row">
      <label for="role">Role</label>
      <select id="role" name="role" required>
        <?php foreach ($ROLES as $r): ?>
          <option value="<?= $r ?>" <?= ($_POST['role'] ?? 'user') === $r ? 'selected' : '' ?>>
            <?= ucfirst($r) ?> — <?= [
              'admin'    => 'full catalog access',
              'uploader' => 'submit species for approval',
              'user'     => 'browse + submit species',
            ][$r] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-actions">
      <a href="manage_users.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Add user</button>
    </div>
  </form>
</div>

<?php admin_layout_close();
