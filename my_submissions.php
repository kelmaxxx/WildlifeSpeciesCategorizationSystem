<?php
session_start();
require_once __DIR__ . '/mongo.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$me = Mongo::oid($_SESSION['user_id']);
$submissions = $me ? $db->find('species', ['uploader_id' => $me], ['sort' => ['created_at' => -1]]) : [];

$justSubmitted = isset($_GET['submitted']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title='My Submissions · Wildlife Explorer'; $css=['public','admin']; include __DIR__ . '/partials/head.php'; ?>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <span class="logo">&#127757;</span>
    Wildlife Explorer
  </div>
  <nav>
    <a class="btn ghost" href="index.php">Browse</a>
    <a class="btn" href="submit_species.php">Submit species</a>
    <a class="btn ghost" href="logout.php">Logout</a>
  </nav>
</header>

<main style="max-width:1000px;margin:2rem auto;padding:0 1.5rem 4rem">
  <h1 style="margin-bottom:.25rem">My submissions</h1>
  <p style="color:var(--slate-500);margin-top:0">Track the approval status of species you've submitted.</p>

  <?php if ($justSubmitted): ?>
    <div class="alert info">&#10003; Your submission was received and is now pending admin review.</div>
  <?php endif; ?>

  <div class="panel" style="margin-top:1.5rem">
    <table class="table">
      <thead>
        <tr><th>Species</th><th>Category</th><th>Habitat</th><th>Submitted</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (count($submissions) === 0): ?>
          <tr><td colspan="5" class="table-empty">
            You haven't submitted anything yet. <a href="submit_species.php">Submit your first species</a>.
          </td></tr>
        <?php else: foreach ($submissions as $s):
          $status = $s->approval_status ?? 'pending';
          $sid    = (string) $s->_id;
          $when   = ($s->created_at ?? null) instanceof MongoDB\BSON\UTCDateTime
                  ? $s->created_at->toDateTime()->format('M j, Y') : '—';
        ?>
          <tr>
            <td>
              <a href="species_detail.php?id=<?= urlencode($sid) ?>">
                <strong><?= htmlspecialchars($s->name ?? '') ?></strong>
              </a>
              <br><em style="color:var(--slate-500);font-size:.8rem"><?= htmlspecialchars($s->scientific_name ?? '') ?></em>
            </td>
            <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
            <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
            <td><?= htmlspecialchars($when) ?></td>
            <td><span class="badge status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
