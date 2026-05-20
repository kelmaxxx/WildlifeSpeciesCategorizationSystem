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
    $confirm  = $_POST['confirm']  ?? '';
    $old['username'] = $username;

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

$page_title = 'Create account — WSCS';
$page_css   = ['auth.css'];
include __DIR__ . '/partials/head-bare.php';
?>

<div class="auth-shell" data-mode="signup">
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
        <a href="login.php"    data-on="0">Sign in</a>
        <a href="register.php" data-on="1">Create account</a>
      </div>

      <h1>Join WSCS<span class="period">.</span></h1>
      <p class="sub">Take a minute to create an account. Once you're in, you can submit a species and watch its journey from pending to published.</p>

      <?php if ($error): ?>
        <div class="alert error" style="margin-bottom:18px"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="auth-form" method="post" action="register.php" novalidate>
        <?= csrf_field() ?>

        <div class="field">
          <label for="signup-username">Username</label>
          <input id="signup-username" name="username" type="text" value="<?= htmlspecialchars($old['username']) ?>"
                 placeholder="your_handle" required autofocus autocomplete="username">
          <span class="helper">Used as the byline on species you contribute.</span>
        </div>

        <div class="field">
          <label for="signup-password">Password</label>
          <input id="signup-password" name="password" type="password" placeholder="At least 6 characters"
                 required autocomplete="new-password">
          <div class="meter" id="meter" data-score="0"><i></i><i></i><i></i><i></i></div>
          <span class="helper" id="meter-helper">Mix letters, numbers, and a symbol for a stronger key.</span>
        </div>

        <div class="field">
          <label for="signup-confirm">Confirm password</label>
          <input id="signup-confirm" name="confirm" type="password" placeholder="Re-enter your password"
                 required autocomplete="new-password">
        </div>

        <div class="submit-block">
          <button type="submit" class="btn btn-primary">
            Create account <span class="arrow" aria-hidden="true"></span>
          </button>
        </div>
      </form>

      <div class="auth-foot">
        <a href="login.php">← Already a member? Sign in</a>
        <a href="index.php">Browse without an account</a>
      </div>
    </div>
  </section>
</div>

<script src="assets/js/password-meter.js"></script>

</body>
</html>
