<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id   = Mongo::oid($_SESSION['admin_id'] ?? null);
$me   = $id ? $db->findById('users', $id) : null;

if (!$me) {
    // Session points to a missing user — force re-login.
    session_destroy();
    header('Location: login.php');
    exit;
}

$success = null;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $newUsername = trim($_POST['username'] ?? '');
    $current     = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $me->password)) {
        $error = 'Your current password is incorrect.';
    } elseif ($newUsername === '') {
        $error = 'Username is required.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPassword !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        $clash = $db->findOne('users', ['username' => $newUsername]);
        if ($clash && (string) $clash->_id !== (string) $id) {
            $error = 'That username is already taken.';
        } else {
            $set = ['username' => $newUsername];
            if ($newPassword !== '') {
                $set['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
            $db->update('users', ['_id' => $id], $set);
            $_SESSION['admin_username'] = $newUsername;
            log_activity($db, 'update', 'user', $newUsername . ' (self)');

            // Reload fresh data
            $me = $db->findById('users', $id);
            $success = $newPassword !== ''
                ? 'Profile updated. Password changed successfully.'
                : 'Profile updated.';
        }
    }
}

admin_layout_open('My Profile', 'profile');
?>

<header class="page-header">
  <div>
    <h1>My profile</h1>
    <p class="subtitle">Update your username or change your password.</p>
  </div>
</header>

<div class="form-card">
  <?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
    <div class="alert info"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-row">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required value="<?= htmlspecialchars($me->username ?? '') ?>">
    </div>

    <hr style="margin:1.5rem 0;border:none;border-top:1px solid var(--slate-100)">
    <h3 style="margin:0 0 1rem;font-size:1rem;color:var(--slate-700)">Change password</h3>

    <div class="form-row">
      <label for="current_password">Current password <span class="hint">required for any change</span></label>
      <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
    </div>
    <div class="form-row">
      <label for="new_password">New password <span class="hint">leave blank to keep current</span></label>
      <input type="password" id="new_password" name="new_password" autocomplete="new-password">
    </div>
    <div class="form-row">
      <label for="confirm_password">Confirm new password</label>
      <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
    </div>

    <div class="form-actions">
      <a href="dashboard.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Save changes</button>
    </div>
  </form>
</div>

<?php admin_layout_close();
