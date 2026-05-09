<?php
session_start();
require_once __DIR__ . '/mongo.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($db->findOne('users', ['username' => $username])) {
        $error = 'That username is already taken.';
    } else {
        $oid = $db->insert('users', [
            'username'   => $username,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'role'       => 'user',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
        ]);
        session_regenerate_id(true);
        $_SESSION['user_id']   = (string) $oid;
        $_SESSION['user_name'] = $username;
        $_SESSION['user_role'] = 'user';
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title='Register · Wildlife Explorer'; $css=['auth']; include __DIR__ . '/partials/head.php'; ?>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="logo-lg">&#127757;</div>
    <h1>Create your account</h1>
    <p class="lead">Join Wildlife Explorer to submit species and track conservation.</p>

    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-row">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-row">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <span class="hint">at least 6 characters</span>
      </div>
      <div class="form-row">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required>
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center;padding:.7rem">Create account</button>
    </form>

    <p class="auth-footer">
      Already have an account? <a href="login.php">Sign in</a><br>
      <a href="index.php">&larr; Back to public site</a>
    </p>
  </div>
</div>
</body>
</html>
