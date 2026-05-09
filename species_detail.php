<?php
session_start();
require_once __DIR__ . '/mongo.php';

$id      = Mongo::oid($_GET['id'] ?? null);
$species = $id ? $db->findById('species', $id) : null;

// Public site only shows approved species. If the looked-up species isn't
// approved, treat it as "not found" — unless the viewer is the uploader or
// an admin (so people can preview their own pending submissions).
$status     = $species->approval_status ?? 'approved';
$viewerId   = $_SESSION['user_id']  ?? null;
$isUploader = $viewerId && isset($species->uploader_id) && (string) $species->uploader_id === $viewerId;
$isAdmin    = !empty($_SESSION['admin_logged_in']);
$canSee     = $species && ($status === 'approved' || $isUploader || $isAdmin);

if (!$canSee) {
    http_response_code(404);
    ?>
    <!DOCTYPE html><html lang="en"><head>
      <?php $title='Species not found'; $css=['public','detail']; include __DIR__ . '/partials/head.php'; ?>
    </head><body>
      <div class="detail-wrap">
        <a class="detail-back" href="index.php">&larr; Back to catalog</a>
        <div class="empty" style="padding:4rem 2rem;background:var(--white);border-radius:14px">
          <div class="empty-icon">&#129301;</div>
          <h3>Species not found</h3>
          <p>It may have been removed or it's still pending approval.</p>
        </div>
      </div>
    </body></html>
    <?php
    exit;
}

// Related: same category, exclude self, only approved, limit 4.
$related = [];
if (!empty($species->category_id)) {
    $related = $db->find('species', [
        'approval_status' => 'approved',
        'category_id'     => $species->category_id,
        '_id'             => ['$ne' => $species->_id],
    ], ['sort' => ['name' => 1], 'limit' => 4]);
}

$cat = strtolower($species->category_name ?? '');
$img = $species->image_url ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title=($species->name ?? 'Species') . ' · Wildlife Explorer'; $css=['public','detail']; include __DIR__ . '/partials/head.php'; ?>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <span class="logo">&#127757;</span>
    Wildlife Explorer
  </div>
  <nav>
    <a class="btn ghost" href="index.php">Browse</a>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <a class="btn ghost" href="my_submissions.php">My submissions</a>
      <a class="btn" href="submit_species.php">Submit species</a>
      <a class="btn ghost" href="logout.php">Logout</a>
    <?php else: ?>
      <a class="btn ghost" href="login.php">Sign in</a>
      <a class="btn" href="admin/login.php">Admin</a>
    <?php endif; ?>
  </nav>
</header>

<main class="detail-wrap">
  <a class="detail-back" href="index.php">&larr; Back to catalog</a>

  <article class="detail-card">
    <div class="detail-hero">
      <?php if ($img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($species->name) ?>">
      <?php else: ?>
        &#128062;
      <?php endif; ?>
      <?php if (!empty($species->is_endangered)): ?>
        <div class="endangered-banner">&#9888; Endangered</div>
      <?php endif; ?>
    </div>

    <div class="detail-body">
      <div class="badges">
        <?php if (!empty($species->category_name)): ?>
          <span class="badge <?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($species->category_name) ?></span>
        <?php endif; ?>
        <?php if ($status !== 'approved'): ?>
          <span class="badge status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
        <?php endif; ?>
      </div>
      <h1><?= htmlspecialchars($species->name ?? 'Unknown species') ?></h1>
      <em><?= htmlspecialchars($species->scientific_name ?? '') ?></em>

      <div class="detail-grid">
        <div class="field">
          <div class="label">Category</div>
          <div class="value"><?= htmlspecialchars($species->category_name ?? '—') ?></div>
        </div>
        <div class="field">
          <div class="label">Habitat</div>
          <div class="value"><?= htmlspecialchars($species->habitat_name ?? '—') ?></div>
        </div>
        <?php if (!empty($species->habitat_location)): ?>
        <div class="field">
          <div class="label">Habitat location</div>
          <div class="value"><?= htmlspecialchars($species->habitat_location) ?></div>
        </div>
        <?php endif; ?>
        <div class="field">
          <div class="label">Conservation status</div>
          <div class="value"><?= !empty($species->is_endangered) ? 'Endangered' : 'Not endangered' ?></div>
        </div>
      </div>
    </div>
  </article>

  <?php if (count($related) > 0): ?>
  <section class="related-section">
    <h2>More <?= htmlspecialchars($species->category_name ?? 'species') ?></h2>
    <div class="card-container related-grid" style="padding:0">
      <?php foreach ($related as $r):
        $rid = (string) $r->_id;
        $rcat = strtolower($r->category_name ?? '');
        $rimg = $r->image_url ?? '';
      ?>
        <a class="card-link" href="species_detail.php?id=<?= urlencode($rid) ?>">
          <article class="card">
            <?php if ($rimg): ?>
              <img class="card-img" src="<?= htmlspecialchars($rimg) ?>" alt="<?= htmlspecialchars($r->name) ?>">
            <?php else: ?>
              <div class="card-img">&#128062;</div>
            <?php endif; ?>
            <div class="card-content">
              <h3><?= htmlspecialchars($r->name ?? '') ?></h3>
              <em><?= htmlspecialchars($r->scientific_name ?? '') ?></em>
            </div>
          </article>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</main>

</body>
</html>
