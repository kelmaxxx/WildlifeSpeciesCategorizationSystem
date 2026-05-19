<?php
session_start();
require_once __DIR__ . '/../mongo.php';
require_once __DIR__ . '/../lib/csrf.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $db->findOne('users', ['username' => $username]);

    if ($user && password_verify($password, $user->password)) {
        if (($user->role ?? '') !== 'admin') {
            $error = 'This account is not an admin.';
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $user->username;
            $_SESSION['admin_id']        = (string) $user->_id;
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title='Admin Login · Wildlife Explorer'; $css=['auth']; $base='../'; include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="logo-lg">&#127757;</div>
    <h1>Admin Login</h1>
    <p class="lead">Sign in to manage species, categories and habitats.</p>

    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-row">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus>
      </div>
      <div class="form-row">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center;padding:.7rem">Sign in</button>
    </form>

    <p class="auth-footer">
      <a href="../index.php">&larr; Back to public site</a>
    </p>
  </div>
</div>
</body>
</html>
