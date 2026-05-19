<?php
session_start();
require_once __DIR__ . '/mongo.php';
require_once __DIR__ . '/lib/csrf.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $db->findOne('users', ['username' => $username]);

    if ($user && password_verify($password, $user->password)) {
        session_regenerate_id(true);
        $role = $user->role ?? 'user';
        if ($role === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $user->username;
            $_SESSION['admin_id']        = (string) $user->_id;
            header('Location: admin/dashboard.php');
            exit;
        }
        $_SESSION['user_id']   = (string) $user->_id;
        $_SESSION['user_name'] = $user->username;
        $_SESSION['user_role'] = $role;
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title='Sign in · Wildlife Explorer'; $css=['auth']; include __DIR__ . '/partials/head.php'; ?>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="logo-lg">&#127757;</div>
    <h1>Welcome back</h1>
    <p class="lead">Sign in to submit species and track your contributions.</p>

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
      No account yet? <a href="register.php">Create one</a><br>
      <a href="index.php">&larr; Back to public site</a>
    </p>
  </div>
</div>
</body>
</html>
