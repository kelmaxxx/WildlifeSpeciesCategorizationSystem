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

// ── Uploader (for the meta rail) ─────────────────────────────────────────
$uploaderName = null;
$uploaderInitial = '?';
if (!empty($species->uploader_id)) {
    $uploader = $db->findById('users', $species->uploader_id);
    if ($uploader && !empty($uploader->username)) {
        $uploaderName    = $uploader->username;
        $uploaderInitial = strtoupper(substr($uploader->username, 0, 1));
    }
}

// ── Related: same category, exclude self, approved only ──────────────────
$related = [];
if (!empty($species->category_id)) {
    $related = $db->find('species', [
        'approval_status' => 'approved',
        'category_id'     => $species->category_id,
        '_id'             => ['$ne' => $species->_id],
    ], ['sort' => ['_id' => -1], 'limit' => 3]);
}

// ── Display helpers ──────────────────────────────────────────────────────
function conservation_dot(bool $end): string {
    return $end ? 'endangered' : 'stable';
}
function conservation_label(bool $end): string {
    return $end ? 'Endangered' : 'Least concern';
}

$isEnd   = !empty($species->is_endangered);
$dot     = conservation_dot($isEnd);
$label   = conservation_label($isEnd);
$img     = $species->image_url ?? '';
$created = null;
if (!empty($species->created_at) && $species->created_at instanceof MongoDB\BSON\UTCDateTime) {
    $created = $species->created_at->toDateTime();
}

$page_title = ($species->name ?? 'Species') . ' — WSCS';
$page_css   = ['detail.css'];
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/topbar.php';
?>

<!-- ─── BREADCRUMB ─────────────────────────────────────────────────────── -->
<section class="frame">
  <div class="crumb">
    <a href="index.php">Browse</a>
    <?php if (!empty($species->category_name)): ?>
      <span class="sep">/</span>
      <a href="index.php?diet=<?= urlencode(strtolower($species->category_name)) ?>#catalog"><?= htmlspecialchars($species->category_name) ?></a>
    <?php endif; ?>
    <?php if (!empty($species->habitat_name)): ?>
      <span class="sep">/</span>
      <a href="index.php?habitat=<?= urlencode($species->habitat_name) ?>#catalog"><?= htmlspecialchars($species->habitat_name) ?></a>
    <?php endif; ?>
    <span class="sep">/</span>
    <span class="here"><?= htmlspecialchars($species->name ?? 'Species') ?></span>
  </div>
</section>

<!-- ─── DETAIL HERO ────────────────────────────────────────────────────── -->
<section class="detail-hero" <?= $img ? 'style="--hero-image: url(\'' . htmlspecialchars($img) . '\')"' : '' ?>>
  <div class="detail-hero-inner frame">
    <div class="hero-top">
      <div class="plate-mark">
        <span class="dot" aria-hidden="true"></span>
        Recorded <?= $created ? htmlspecialchars($created->format('j F Y')) : date('j F Y') ?>
      </div>
      <?php if ($status !== 'approved'): ?>
        <span class="status-cap" data-s="vulnerable">
          <?= htmlspecialchars(ucfirst($status)) ?> · awaiting review
        </span>
      <?php else: ?>
        <span class="status-cap" data-s="<?= $dot ?>">
          <?= $label ?>
        </span>
      <?php endif; ?>
    </div>
    <div class="hero-bot">
      <h1 class="common display"><?= htmlspecialchars($species->name ?? 'Unknown') ?><span class="period">.</span></h1>
      <div class="lat-name"><?= htmlspecialchars($species->scientific_name ?? '') ?></div>
      <div class="taxonomy">
        <span><?= htmlspecialchars($species->category_name ?? 'Animalia') ?></span>
        <?php if (!empty($species->habitat_name)): ?>
          <span class="sep">·</span>
          <span><?= htmlspecialchars($species->habitat_name) ?></span>
        <?php endif; ?>
        <?php if (!empty($species->habitat_location)): ?>
          <span class="sep">·</span>
          <span><?= htmlspecialchars($species->habitat_location) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ─── BODY ARTICLE + META RAIL ───────────────────────────────────────── -->
<section class="frame">
  <div class="article-grid">
    <article class="prose">
      <p class="lede">
        <?= htmlspecialchars($species->name ?? '') ?> — <em><?= htmlspecialchars($species->scientific_name ?? '') ?></em> — is a
        <?= htmlspecialchars(strtolower($species->category_name ?? 'species')) ?>
        recorded in <?= htmlspecialchars($species->habitat_name ?? 'the wild') ?><?= !empty($species->habitat_location) ? ' (' . htmlspecialchars($species->habitat_location) . ')' : '' ?>.
      </p>

      <div class="first">
        <p>
          This entry covers a
          <?= htmlspecialchars(strtolower($species->category_name ?? 'species')) ?>
          observed across <?= htmlspecialchars($species->habitat_name ?? 'multiple habitats') ?>.
          <?= $isEnd
                ? 'It is currently listed as endangered — populations are in decline and active conservation is required to sustain it in the wild.'
                : 'Populations are presently considered stable; the species remains a familiar sight within its habitat.' ?>
        </p>

        <?php if (!empty($species->habitat_location)): ?>
          <p>
            Range observations place the species across
            <?= htmlspecialchars($species->habitat_location) ?>,
            tracking the broader distribution of <?= htmlspecialchars($species->habitat_name ?? 'this habitat') ?> systems.
          </p>
        <?php endif; ?>
      </div>

      <h3>Field summary</h3>
      <ul>
        <li><strong>Common name —</strong> <?= htmlspecialchars($species->name ?? '—') ?></li>
        <li><strong>Scientific name —</strong> <em><?= htmlspecialchars($species->scientific_name ?? '—') ?></em></li>
        <li><strong>Diet —</strong> <?= htmlspecialchars($species->category_name ?? '—') ?></li>
        <li><strong>Habitat —</strong> <?= htmlspecialchars($species->habitat_name ?? '—') ?></li>
        <?php if (!empty($species->habitat_location)): ?>
          <li><strong>Range —</strong> <?= htmlspecialchars($species->habitat_location) ?></li>
        <?php endif; ?>
        <li><strong>Conservation status —</strong> <?= $label ?></li>
      </ul>
    </article>

    <aside class="meta-rail">
      <div class="meta-section">
        <h4>Conservation status</h4>
        <div class="headline"><?= $label ?></div>
        <div class="sub">
          <?= $isEnd
                ? 'Listed as endangered in the catalog. Treat sightings as significant — note location, date, and any threats observed.'
                : 'Populations stable as currently catalogued; not flagged as at-risk.' ?>
        </div>
      </div>

      <div class="meta-section">
        <h4>Range</h4>
        <dl class="meta-list">
          <dt>Habitat</dt><dd><?= htmlspecialchars($species->habitat_name ?? '—') ?></dd>
          <?php if (!empty($species->habitat_location)): ?>
            <dt>Region</dt><dd><?= htmlspecialchars($species->habitat_location) ?></dd>
          <?php endif; ?>
          <dt>Diet</dt><dd><?= htmlspecialchars($species->category_name ?? '—') ?></dd>
        </dl>
      </div>

      <?php if ($uploaderName): ?>
      <div class="meta-section">
        <h4>First catalogued by</h4>
        <div class="contributor">
          <div class="av" aria-hidden="true"><?= htmlspecialchars($uploaderInitial) ?></div>
          <div>
            <div class="who"><?= htmlspecialchars($uploaderName) ?></div>
            <div class="when">
              <?= $created ? htmlspecialchars($created->format('j M Y')) : 'Date not recorded' ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="meta-section">
        <h4>Record</h4>
        <dl class="meta-list">
          <dt>Status</dt><dd><?= htmlspecialchars(ucfirst($status)) ?></dd>
          <?php if ($created): ?>
            <dt>Catalogued</dt><dd><?= htmlspecialchars($created->format('j M Y')) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </aside>
  </div>
</section>

<!-- ─── RELATED ────────────────────────────────────────────────────────── -->
<?php if (count($related) > 0): ?>
<section class="frame">
  <div class="adjacent">
    <div class="section-head">
      <div>
        <div class="eyebrow">Related species</div>
        <h2 class="display">Same category.</h2>
      </div>
      <p class="desc">
        Other <?= htmlspecialchars(strtolower($species->category_name ?? 'species')) ?> entries recorded in the catalog.
      </p>
    </div>

    <div class="grid">
      <?php foreach ($related as $r):
          $rEnd  = !empty($r->is_endangered);
          $rDot  = conservation_dot($rEnd);
          $rHasPhoto = !empty($r->image_url);
      ?>
        <a class="card" href="species_detail.php?id=<?= urlencode((string) $r->_id) ?>" tabindex="0">
          <div class="card-img<?= $rHasPhoto ? ' has-photo' : '' ?>">
            <?php if ($rHasPhoto): ?>
              <div class="img" style="background-image:url('<?= htmlspecialchars($r->image_url) ?>')"></div>
            <?php else: ?>
              <div class="placeholder">
                <div class="silhouette" aria-hidden="true"></div>
                <div style="font-family:var(--serif);font-style:italic;font-size:13px;letter-spacing:0;text-transform:none;color:var(--ink-soft)">
                  <?= htmlspecialchars($r->scientific_name ?? '') ?>
                </div>
                <div style="margin-top:6px;color:var(--ink-mute)">Photograph forthcoming</div>
              </div>
            <?php endif; ?>
            <span class="status-dot" data-s="<?= $rDot ?>" title="<?= conservation_label($rEnd) ?>"></span>
          </div>
          <div>
            <h3 class="common"><?= htmlspecialchars($r->name ?? '') ?></h3>
            <div class="lat-name"><?= htmlspecialchars($r->scientific_name ?? '') ?></div>
          </div>
          <div class="card-foot">
            <span class="status" data-s="<?= $rDot ?>"><?= conservation_label($rEnd) ?></span>
            <span><?= htmlspecialchars($r->habitat_name ?? '—') ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
