<?php
require_once __DIR__ . '/public_auth.php';
require_user();

$me = Mongo::oid($_SESSION['user_id']);
$submissions = $me ? $db->find('species', ['uploader_id' => $me], ['sort' => ['created_at' => -1]]) : [];

$justSubmitted = isset($_GET['submitted']);

$counts = [
    'pending'  => 0,
    'approved' => 0,
    'rejected' => 0,
];
foreach ($submissions as $s) {
    $st = $s->approval_status ?? 'pending';
    if (isset($counts[$st])) $counts[$st]++;
}

$page_title = 'My submissions — WSCS';
$page_css   = ['admin.css'];
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/topbar.php';
?>

<section class="frame">
  <div class="crumb">
    <a href="index.php">Browse</a>
    <span class="sep">/</span>
    <span class="here">My submissions</span>
  </div>

  <div class="page-head">
    <div>
      <div class="eyebrow">Your contributions</div>
      <h1 class="display">Your <i class="accent">submissions.</i></h1>
    </div>
    <p class="intro">
      Every species you've sent to the catalog, with its current review status.
      Pending entries are awaiting review; approved ones are live in the public catalog.
    </p>
  </div>

  <?php if ($justSubmitted): ?>
    <div class="alert info" style="margin-bottom:24px">
      ✓ Your submission was received and is now <b>pending</b> editorial review.
    </div>
  <?php endif; ?>

  <div class="stats" style="margin-bottom:32px">
    <div class="stat">
      <div class="num"><?= count($submissions) ?></div>
      <div class="label">Total submitted</div>
      <div class="delta">All time</div>
    </div>
    <div class="stat">
      <div class="num"><?= $counts['pending'] ?></div>
      <div class="label">Pending review</div>
      <div class="delta" style="color:var(--status-vuln)">Awaiting editor</div>
    </div>
    <div class="stat">
      <div class="num"><?= $counts['approved'] ?></div>
      <div class="label">Approved</div>
      <div class="delta" style="color:var(--status-stable)">Live in catalog</div>
    </div>
    <div class="stat">
      <div class="num"><?= $counts['rejected'] ?></div>
      <div class="label">Rejected</div>
      <div class="delta" style="color:#8b2c2c">Needs revision</div>
    </div>
  </div>

  <?php if (count($submissions) === 0): ?>
    <div class="empty-state" style="padding:64px 0">
      <div class="placeholder" style="height:240px">
        <div>No submissions yet.</div>
        <div style="font-family:var(--serif);font-style:italic;font-size:14px;color:var(--ink-soft);letter-spacing:0;text-transform:none;margin-top:6px">
          When you contribute a species, it will appear here.
        </div>
        <a href="submit_species.php" class="btn btn-primary" style="margin-top:18px">
          Contribute a species <span class="arrow" aria-hidden="true"></span>
        </a>
      </div>
    </div>
  <?php else: ?>
    <table class="tbl">
      <thead>
        <tr>
          <th>Species</th>
          <th>Diet</th>
          <th>Habitat</th>
          <th>Submitted</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($submissions as $s):
          $status = $s->approval_status ?? 'pending';
          $sid    = (string) $s->_id;
          $when   = ($s->created_at ?? null) instanceof MongoDB\BSON\UTCDateTime
                  ? $s->created_at->toDateTime()->format('j M Y') : '—';
          $img    = $s->image_url ?? '';
        ?>
          <tr>
            <td>
              <div class="spec">
                <div class="thumb">
                  <?php if ($img): ?>
                    <div class="img" style="background-image:url('<?= htmlspecialchars($img) ?>')"></div>
                  <?php endif; ?>
                </div>
                <div class="name">
                  <a class="common" href="species_detail.php?id=<?= urlencode($sid) ?>" style="color:inherit;text-decoration:none">
                    <?= htmlspecialchars($s->name ?? 'Unknown') ?>
                  </a>
                  <div class="latin"><?= htmlspecialchars($s->scientific_name ?? '') ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
            <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
            <td class="when"><?= htmlspecialchars($when) ?></td>
            <td><span class="status" data-s="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
