<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$ROLES = ['admin', 'user'];

$id   = Mongo::oid($_GET['id'] ?? null);
$user = $id ? $db->findById('users', $id) : null;

if (!$user) {
    admin_layout_open('User not found', 'users');
    echo '<div class="alert error">User not found.</div>';
    echo '<a href="manage_users.php" class="btn">&larr; Back</a>';
    admin_layout_close();
    exit;
}

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

<header class="page-header">
  <div>
    <h1>Edit user</h1>
    <p class="subtitle">Leave password blank to keep the existing one.</p>
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
      <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user->username ?? '') ?>">
    </div>
    <div class="form-row">
      <label for="password">Password <span class="hint">leave blank to keep current</span></label>
      <input type="password" id="password" name="password" autocomplete="new-password">
    </div>
    <div class="form-row">
      <label for="role">Role</label>
      <select id="role" name="role" required>
        <?php foreach ($ROLES as $r): ?>
          <option value="<?= $r ?>" <?= ($user->role ?? 'user') === $r ? 'selected' : '' ?>>
            <?= ucfirst($r) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-actions">
      <a href="manage_users.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Save changes</button>
    </div>
  </form>
</div>

<?php admin_layout_close();
