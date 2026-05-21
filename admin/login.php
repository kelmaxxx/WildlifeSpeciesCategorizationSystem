<?php
session_start();
require_once __DIR__ . '/../mongo.php';
require_once __DIR__ . '/../lib/csrf.php';

$error = null;
$old   = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $old['username'] = $username;

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

$page_title = 'Editor sign-in — WSCS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/auth.css">
  <script>
    (function () {
      var saved = null;
      try { saved = localStorage.getItem('wscs-theme'); } catch (e) {}
      var theme = (saved === 'forest' || saved === 'light') ? saved : 'light';
      document.addEventListener('DOMContentLoaded', function () {
        document.body.setAttribute('data-theme', theme);
      });
    })();
  </script>
</head>
<body data-theme="light">

<div class="auth-shell" data-mode="signin">
  <aside class="auth-plate">
    <div class="plate-top">
      <a href="../index.php" class="plate-brand" aria-label="Wildlife Species Categorization System — home">
        <img class="logo" src="../images/logo.svg" alt="" aria-hidden="true">
        <span class="wordmark">WSCS</span>
        <span class="tag">Admin</span>
      </a>
    </div>
    <div class="plate-bot">
      <h2>The editor's<br><i>desk.</i></h2>
      <blockquote>
        Every published entry passes through an editor — verifying submissions and approving species before they reach the public catalog. Sign in to take your seat.
      </blockquote>
    </div>
  </aside>

  <section class="auth-form-col">
    <div class="auth-top-right">
      <a href="../index.php">← Back to public site</a>
    </div>

    <div class="auth-card">
      <div class="auth-eyebrow"><span class="num">Editor access</span></div>

      <h1>Editor sign-in<span class="period">.</span></h1>
      <p class="sub">Restricted to editors. Use your editor credentials to manage approvals, species, and contributors.</p>

      <?php if ($error): ?>
        <div class="alert error" style="margin-bottom:18px"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="auth-form" method="post" novalidate>
        <?= csrf_field() ?>

        <div class="field">
          <label for="admin-username">Username</label>
          <input id="admin-username" name="username" type="text" value="<?= htmlspecialchars($old['username']) ?>"
                 placeholder="editor_handle" required autofocus autocomplete="username">
        </div>

        <div class="field">
          <label for="admin-password">Password</label>
          <input id="admin-password" name="password" type="password" placeholder="••••••••"
                 required autocomplete="current-password">
        </div>

        <div class="submit-block">
          <button type="submit" class="btn btn-primary">
            Sign in to admin <span class="arrow" aria-hidden="true"></span>
          </button>
        </div>
      </form>

      <div class="auth-foot">
        <a href="../login.php">Not an editor? Member sign-in →</a>
        <a href="../index.php">Browse the catalog</a>
      </div>
    </div>
  </section>
</div>

</body>
</html>
