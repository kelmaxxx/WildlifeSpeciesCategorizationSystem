<?php
session_start();
require_once __DIR__ . '/mongo.php';
require_once __DIR__ . '/lib/csrf.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;
$old   = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $old['username'] = $username;

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

$page_title = 'Sign in — WSCS';
$page_css   = ['auth.css'];
include __DIR__ . '/partials/head-bare.php';
?>

<div class="auth-shell" data-mode="signin">
  <aside class="auth-plate">
    <div class="plate-top">
      <a href="index.php" class="plate-brand" aria-label="Wildlife Species Categorization System — home">
        <img class="logo" src="images/logo.svg" alt="" aria-hidden="true">
        <span class="wordmark">WSCS</span>
      </a>
    </div>
    <div class="plate-bot">
      <h2>A community<br>of <i>contributors.</i></h2>
      <blockquote>
        Each entry begins with a single observer — patient, curious, willing to put their name to what they saw. Without contributors, the catalog is only a list.
      </blockquote>
    </div>
  </aside>

  <section class="auth-form-col">
    <div class="auth-top-right">
      <a href="index.php">← Back to browse</a>
    </div>

    <div class="auth-card">
      <div class="auth-eyebrow"><span class="num">Member access</span></div>

      <div class="auth-tabs" role="tablist">
        <a href="login.php"    data-on="1">Sign in</a>
        <a href="register.php" data-on="0">Create account</a>
      </div>

      <h1>Welcome back<span class="period">.</span></h1>
      <p class="sub">Sign in to submit species, track your contributions, and pick up where you left off.</p>

      <?php if ($error): ?>
        <div class="alert error" style="margin-bottom:18px"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="auth-form" method="post" action="login.php" novalidate>
        <?= csrf_field() ?>

        <div class="field">
          <label for="signin-username">Username</label>
          <input id="signin-username" name="username" type="text" value="<?= htmlspecialchars($old['username']) ?>"
                 placeholder="your_handle" required autofocus autocomplete="username">
        </div>

        <div class="field">
          <label for="signin-password">Password</label>
          <input id="signin-password" name="password" type="password" placeholder="••••••••"
                 required autocomplete="current-password">
        </div>

        <div class="submit-block">
          <button type="submit" class="btn btn-primary">
            Sign in <span class="arrow" aria-hidden="true"></span>
          </button>
        </div>
      </form>

      <div class="auth-foot">
        <a href="register.php">New here? Create an account →</a>
        <a href="index.php">Browse without an account</a>
      </div>
    </div>
  </section>
</div>

</body>
</html>
