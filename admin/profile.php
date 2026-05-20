<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id = Mongo::oid($_SESSION['admin_id'] ?? null);
$me = $id ? $db->findById('users', $id) : null;

if (!$me) {
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

            $me = $db->findById('users', $id);
            $success = $newPassword !== ''
                ? 'Profile updated. Password changed successfully.'
                : 'Profile updated.';
        }
    }
}

admin_layout_open('My Profile', 'profile');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Editor's desk · your account
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:44px;line-height:1.05;letter-spacing:-.018em;margin:8px 0 0;color:var(--ink);font-weight:500">
      <i style="color:var(--forest-deep)">Profile.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:16px;color:var(--ink-soft);margin:10px 0 0">
      Update your username, or change your password.
    </p>
  </div>
</header>

<?php if ($error): ?>
  <div class="alert error" style="margin-bottom:24px"><?= htmlspecialchars($error) ?></div>
<?php elseif ($success): ?>
  <div class="alert info" style="margin-bottom:24px"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" class="contribute" style="max-width:560px">
  <?= csrf_field() ?>

  <fieldset class="fset">
    <legend>
      <span class="num">§ 01</span>
      <h2>Identity</h2>
      <span class="req">Required</span>
    </legend>
    <div class="frow">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required value="<?= htmlspecialchars($me->username ?? '') ?>">
    </div>
  </fieldset>

  <fieldset class="fset">
    <legend>
      <span class="num">§ 02</span>
      <h2>Change password</h2>
      <span class="req">Optional</span>
    </legend>
    <div class="frow">
      <label for="current_password">Current password <span class="opt">Required for any change</span></label>
      <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
    </div>
    <div class="frow">
      <label for="new_password">New password <span class="opt">Leave blank to keep current</span></label>
      <input type="password" id="new_password" name="new_password" autocomplete="new-password">
    </div>
    <div class="frow">
      <label for="confirm_password">Confirm new password</label>
      <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
    </div>
  </fieldset>

  <div class="submit-row">
    <a href="dashboard.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">Save changes <span class="arrow" aria-hidden="true"></span></button>
  </div>
</form>

<?php admin_layout_close(); ?>
