<?php
session_start();
require_once __DIR__ . '/mongo.php';

$id      = Mongo::oid($_GET['id'] ?? null);
$species = $id ? $db->findById('species', $id) : null;

$status     = $species->approval_status ?? 'approved';
$viewerId   = $_SESSION['user_id']  ?? null;
$isUploader = $viewerId && isset($species->uploader_id) && (string) $species->uploader_id === $viewerId;
$isAdmin    = !empty($_SESSION['admin_logged_in']);
$canSee     = $species && ($status === 'approved' || $isUploader || $isAdmin);

if (!$canSee) {
    http_response_code(404);
    $page_title = 'Species not found — WSCS';
    $page_css   = ['detail.css'];
    include __DIR__ . '/partials/head.php';
    include __DIR__ . '/partials/topbar.php';
    ?>
    <section class="frame">
      <div class="crumb">
        <a href="index.php">Browse</a>
        <span class="sep">/</span>
        <span class="here">Not found</span>
      </div>
      <div class="empty-state" style="padding:64px 0">
        <div class="placeholder" style="height:240px">
          <div>No record at this address.</div>
          <div style="font-family:var(--serif);font-style:italic;font-size:14px;color:var(--ink-soft);letter-spacing:0;text-transform:none;margin-top:6px">
            It may have been removed, or it's still awaiting review.
          </div>
        </div>
      </div>
    </section>
    <?php include __DIR__ . '/partials/footer.php';
    exit;
}

$uploaderName    = null;
$uploaderInitial = '?';
if (!empty($species->uploader_id)) {
    $uploader = $db->findById('users', $species->uploader_id);
    if ($uploader && !empty($uploader->username)) {
        $uploaderName    = $uploader->username;
        $uploaderInitial = strtoupper(substr($uploader->username, 0, 1));
    }
}

$isEnd  = !empty($species->is_endangered);
$label  = $isEnd ? 'Endangered' : 'Least concern';
$dot    = $isEnd ? 'endangered' : 'stable';
$img    = $species->image_url ?? '';
$diet   = $species->category_name   ?? '';
$hab    = $species->habitat_name    ?? '';
$range  = $species->habitat_location ?? '';

$page_title = ($species->name ?? 'Species') . ' — WSCS';
$page_css   = ['detail.css'];
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/topbar.php';
?>

<section class="frame">
  <div class="crumb">
    <a href="index.php">Browse</a>
    <?php if ($diet !== ''): ?>
      <span class="sep">/</span>
      <a href="index.php?diet=<?= urlencode(strtolower($diet)) ?>#catalog"><?= htmlspecialchars($diet) ?></a>
    <?php endif; ?>
    <?php if ($hab !== ''): ?>
      <span class="sep">/</span>
      <a href="index.php?habitat=<?= urlencode($hab) ?>#catalog"><?= htmlspecialchars($hab) ?></a>
    <?php endif; ?>
    <span class="sep">/</span>
    <span class="here"><?= htmlspecialchars($species->name ?? 'Species') ?></span>
  </div>
</section>

<section class="frame detail-hero-frame">
  <figure class="d-hero<?= $img ? '' : ' is-empty' ?>">
    <?php if ($img): ?>
      <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($species->name ?? '') ?>">
    <?php else: ?>
      <div class="d-hero-ph">
        <div class="silhouette" aria-hidden="true"></div>
        <div class="cap">Photograph not provided</div>
      </div>
    <?php endif; ?>
    <?php if ($status !== 'approved'): ?>
      <span class="d-status-cap" data-s="pending">
        <?= htmlspecialchars(ucfirst($status)) ?> · awaiting review
      </span>
    <?php else: ?>
      <span class="d-status-cap" data-s="<?= $dot ?>">
        <?= $label ?>
      </span>
    <?php endif; ?>
  </figure>
</section>

<section class="frame">
  <header class="d-title">
    <h1><?= htmlspecialchars($species->name ?? 'Unknown') ?></h1>
    <div class="sci"><?= htmlspecialchars($species->scientific_name ?? '') ?></div>
    <div class="chips">
      <?php if ($diet  !== ''): ?><span class="chip-line"><i aria-hidden="true">◆</i><?= htmlspecialchars($diet) ?></span><?php endif; ?>
      <?php if ($hab   !== ''): ?><span class="chip-line"><i aria-hidden="true">▲</i><?= htmlspecialchars($hab) ?></span><?php endif; ?>
      <?php if ($range !== ''): ?><span class="chip-line"><i aria-hidden="true">○</i><?= htmlspecialchars($range) ?></span><?php endif; ?>
    </div>
  </header>
</section>

<section class="frame d-body">
  <div class="d-grid">
    <article class="d-main">
      <div class="d-section">
        <h2 class="d-h2">Quick facts</h2>
        <dl class="d-facts">
          <div class="d-fact">
            <dt>Name</dt>
            <dd><?= htmlspecialchars($species->name ?? '—') ?></dd>
          </div>
          <div class="d-fact">
            <dt>Scientific name</dt>
            <dd><em><?= htmlspecialchars($species->scientific_name ?? '—') ?></em></dd>
          </div>
          <div class="d-fact">
            <dt>Diet</dt>
            <dd><?= $diet !== '' ? htmlspecialchars($diet) : '—' ?></dd>
          </div>
          <div class="d-fact">
            <dt>Habitat</dt>
            <dd><?= $hab !== '' ? htmlspecialchars($hab) : '—' ?></dd>
          </div>
          <div class="d-fact">
            <dt>Range</dt>
            <dd><?= $range !== '' ? htmlspecialchars($range) : '—' ?></dd>
          </div>
          <div class="d-fact">
            <dt>Status</dt>
            <dd><?= $label ?></dd>
          </div>
        </dl>
      </div>
    </article>

    <aside class="d-rail">
      <div class="rail-card rail-status" data-s="<?= $dot ?>">
        <div class="rail-label">Conservation status</div>
        <div class="rail-headline"><?= $label ?></div>
        <div class="rail-sub">
          <?= $isEnd
                ? 'Listed as endangered. Populations are declining and require active conservation.'
                : 'Populations are considered stable; this species is not currently flagged as at risk.' ?>
        </div>
      </div>

      <?php if ($uploaderName): ?>
        <div class="rail-card">
          <div class="rail-label">Submitted by</div>
          <div class="rail-uploader">
            <span class="av" aria-hidden="true"><?= htmlspecialchars($uploaderInitial) ?></span>
            <div>
              <div class="who"><?= htmlspecialchars($uploaderName) ?></div>
              <div class="when">Contributor</div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
