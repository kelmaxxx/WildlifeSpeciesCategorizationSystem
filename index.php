<?php
session_start();
require_once __DIR__ . '/mongo.php';

// ── Inputs ───────────────────────────────────────────────────────────────
$q       = trim($_GET['q']       ?? '');
$diet    = $_GET['diet']    ?? 'all';      // all|carnivore|herbivore|omnivore
$status  = $_GET['status']  ?? 'all';      // all|stable|endangered
$habitat = $_GET['habitat'] ?? 'all';      // all|<habitat-name>
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;

// ── Build the MongoDB filter ─────────────────────────────────────────────
$mongoFilter = ['approval_status' => 'approved'];

if ($q !== '') {
    $regex = new MongoDB\BSON\Regex(preg_quote($q, '/'), 'i');
    $mongoFilter['$or'] = [
        ['name'            => $regex],
        ['scientific_name' => $regex],
        ['category_name'   => $regex],
        ['habitat_name'    => $regex],
    ];
}
if ($diet !== 'all') {
    $mongoFilter['category_name'] = ucfirst($diet);
}
if ($status === 'endangered') $mongoFilter['is_endangered'] = true;
if ($status === 'stable')     $mongoFilter['is_endangered'] = false;
if ($habitat !== 'all')       $mongoFilter['habitat_name']  = $habitat;

// ── Counts for hero/stats band ───────────────────────────────────────────
$approvedFilter   = ['approval_status' => 'approved'];
$totalSpecies     = $db->count('species',  $approvedFilter);
$endangeredCount  = $db->count('species',  $approvedFilter + ['is_endangered' => true]);
$habitatTotal     = $db->count('habitats');
$contributorCount = $db->count('users');
$atRiskPct        = $totalSpecies > 0 ? (int) round($endangeredCount * 100 / $totalSpecies) : 0;

// ── Counts for filter chips ──────────────────────────────────────────────
$counts = [
    'diet' => [
        'all'       => $db->count('species', $approvedFilter),
        'carnivore' => $db->count('species', $approvedFilter + ['category_name' => 'Carnivore']),
        'herbivore' => $db->count('species', $approvedFilter + ['category_name' => 'Herbivore']),
        'omnivore'  => $db->count('species', $approvedFilter + ['category_name' => 'Omnivore']),
    ],
    'status' => [
        'all'        => $db->count('species', $approvedFilter),
        'stable'     => $db->count('species', $approvedFilter + ['is_endangered' => false]),
        'endangered' => $endangeredCount,
    ],
];

// Habitat chips — top 6 habitats by species count
$habitatRows = $db->find('habitats', [], ['sort' => ['name' => 1]]);
$habitatChips = [['id' => 'all', 'label' => 'Any habitat', 'count' => $totalSpecies]];
foreach ($habitatRows as $h) {
    $name = $h->name ?? '';
    if ($name === '') continue;
    $habitatChips[] = [
        'id'    => $name,
        'label' => $name,
        'count' => $db->count('species', $approvedFilter + ['habitat_name' => $name]),
    ];
}
usort($habitatChips, fn($a, $b) => ($a['id'] === 'all' ? -1 : ($b['id'] === 'all' ? 1 : $b['count'] - $a['count'])));
$habitatChips = array_slice($habitatChips, 0, 7);

// ── Featured "Specimen of the week" — newest endangered approved species ──
$featured = $db->findOne('species', $approvedFilter + ['is_endangered' => true], ['sort' => ['_id' => -1]])
         ?? $db->findOne('species', $approvedFilter, ['sort' => ['_id' => -1]]);

// ── Paginated list ───────────────────────────────────────────────────────
$total      = $db->count('species', $mongoFilter);
$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$species    = $db->find('species', $mongoFilter, [
    'sort'  => ['_id' => -1],
    'skip'  => ($page - 1) * $perPage,
    'limit' => $perPage,
]);
$start = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$end   = min($total, $page * $perPage);

// ── Helpers ──────────────────────────────────────────────────────────────
function plate_num($id): string {
    $hex = substr((string) $id, -3);
    return strtoupper($hex);
}
function conservation_dot(bool $endangered): string {
    return $endangered ? 'endangered' : 'stable';
}
function conservation_label(bool $endangered): string {
    return $endangered ? 'Endangered' : 'Least concern';
}
function chip_url(string $key, string $value): string {
    $params = $_GET;
    $params[$key] = $value;
    unset($params['page']);
    return '?' . http_build_query($params);
}
function page_url(int $n): string {
    $params = $_GET;
    $params['page'] = $n;
    return '?' . http_build_query($params);
}

$page_title = 'Wildlife Catalog — A living index of the species we share this world with.';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/topbar.php';
?>

<!-- ─── HERO ─────────────────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-inner frame">
    <div class="hero-text">
      <span class="hero-pill">A living catalog of wildlife</span>
      <div class="hero-issue" style="margin-top:22px">
        <span class="dot" aria-hidden="true"></span>
        <span class="eyebrow">Vol. III · No. <?= htmlspecialchars((string) $totalSpecies) ?> · <?= date('F Y') ?></span>
      </div>
      <h1 class="display">
        A living index<br>
        of the species we<br>
        <i class="accent">share this world</i> with.
      </h1>
      <p class="lede">
        A community-built catalog of the world's wildlife — sightings, descriptions, habitat ranges, and conservation status, recorded by the people who care for them.
      </p>
      <div class="feat-actions">
        <a href="#catalog" class="btn btn-primary">Browse the catalog <span class="arrow" aria-hidden="true"></span></a>
        <a href="submit_species.php" class="btn btn-ghost">Contribute a sighting</a>
      </div>
    </div>
    <div class="hero-foot">
      <div class="hero-plate">
        Plate №<?= $featured ? plate_num($featured->_id) : '000' ?> · Field №R-H/<?= date('Y') ?>
        <span class="lat"><?= $featured ? htmlspecialchars($featured->scientific_name ?? '') : '' ?></span>
      </div>
      <div class="hero-credit">
        <b>Photograph · Field team</b>
        Recorded · <?= date('F') ?>
      </div>
    </div>
  </div>
</section>

<!-- ─── STATS BAND ───────────────────────────────────────────────────── -->
<section class="frame">
  <div class="stats">
    <div class="stat">
      <div class="num"><?= number_format($totalSpecies) ?></div>
      <div class="label">Species catalogued</div>
      <div class="delta">Approved &amp; published</div>
    </div>
    <div class="stat">
      <div class="num"><?= (int) $habitatTotal ?></div>
      <div class="label">Habitats indexed</div>
      <div class="delta">Across the globe</div>
    </div>
    <div class="stat">
      <div class="num"><?= number_format($contributorCount) ?></div>
      <div class="label">Contributors</div>
      <div class="delta">Registered field hands</div>
    </div>
    <div class="stat">
      <div class="num"><?= $atRiskPct ?><span class="small">%</span></div>
      <div class="label">At-risk status</div>
      <div class="delta" style="color:var(--status-end)">Listed as endangered</div>
    </div>
  </div>
</section>

<!-- ─── FEATURED SPECIMEN ────────────────────────────────────────────── -->
<?php if ($featured): $fImg = $featured->image_url ?? ''; ?>
<section class="frame">
  <div class="section-head">
    <div class="title-block">
      <div class="eyebrow">Specimen of the week · №<?= plate_num($featured->_id) ?></div>
      <h2 class="display">From the field journal.</h2>
    </div>
    <p class="desc">
      Each week our editors choose one specimen for a closer look — the bird, beast, or beetle that earned a longer page in the journal.
    </p>
  </div>
  <div class="featured">
    <figure class="feat-photo">
      <div class="img" <?= $fImg ? 'style="background-image:url(\'' . htmlspecialchars($fImg) . '\')"' : '' ?>></div>
      <figcaption class="plate">
        <div>Plate №<?= plate_num($featured->_id) ?></div>
        <div class="lat"><?= htmlspecialchars($featured->scientific_name ?? '') ?></div>
      </figcaption>
    </figure>
    <div class="feat-body">
      <div class="eyebrow"><?= htmlspecialchars($featured->category_name ?? 'Animalia') ?> · <?= htmlspecialchars($featured->habitat_name ?? '') ?></div>
      <h3><?= htmlspecialchars($featured->name ?? 'Unknown') ?>.</h3>
      <div class="lat-name"><?= htmlspecialchars($featured->scientific_name ?? '') ?></div>
      <p class="summary">
        A <?= htmlspecialchars(strtolower($featured->category_name ?? 'species')) ?> recorded in <?= htmlspecialchars($featured->habitat_name ?? 'the wild') ?><?= !empty($featured->habitat_location) ? ' (' . htmlspecialchars($featured->habitat_location) . ')' : '' ?>. <?= !empty($featured->is_endangered) ? 'Currently listed as endangered — populations are declining and require active conservation.' : 'Populations are considered stable; the species remains a familiar sight in its habitat.' ?>
      </p>
      <dl class="feat-meta">
        <div><dt>Diet</dt><dd><?= htmlspecialchars($featured->category_name ?? '—') ?></dd></div>
        <div><dt>Range</dt><dd><?= htmlspecialchars($featured->habitat_location ?? '—') ?></dd></div>
        <div><dt>Habitat</dt><dd><?= htmlspecialchars($featured->habitat_name ?? '—') ?></dd></div>
        <div><dt>Status</dt><dd><?= conservation_label(!empty($featured->is_endangered)) ?></dd></div>
        <div><dt>Plate №</dt><dd><?= plate_num($featured->_id) ?></dd></div>
        <div><dt>Volume</dt><dd>Vol. III · <?= date('Y') ?></dd></div>
      </dl>
      <div class="feat-actions">
        <a href="species_detail.php?id=<?= urlencode((string) $featured->_id) ?>" class="btn btn-primary">
          Read the full entry <span class="arrow" aria-hidden="true"></span>
        </a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ─── SEARCH + FILTERS ─────────────────────────────────────────────── -->
<section class="frame">
  <div class="section-head">
    <div class="title-block">
      <div class="eyebrow">Index · Search · Filter</div>
      <h2 class="display">Find your specimen.</h2>
    </div>
    <p class="desc">
      Three axes — diet, conservation status, and habitat — narrow the catalog to what you're looking for.
    </p>
  </div>

  <form class="controls" method="get" action="#catalog">
    <input type="hidden" name="diet"    value="<?= htmlspecialchars($diet) ?>">
    <input type="hidden" name="status"  value="<?= htmlspecialchars($status) ?>">
    <input type="hidden" name="habitat" value="<?= htmlspecialchars($habitat) ?>">

    <div>
      <div class="search">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
               placeholder="Search species, scientific name, habitat…"
               aria-label="Search the catalog">
        <span class="icon" aria-hidden="true"></span>
      </div>
      <div class="search-hint">
        <?= number_format($totalSpecies) ?> records · index updated daily
      </div>
    </div>

    <div class="filter-groups">
      <?php
      $dietChips = [
          ['id' => 'all',       'label' => 'All',       'count' => $counts['diet']['all']],
          ['id' => 'carnivore', 'label' => 'Carnivore', 'count' => $counts['diet']['carnivore']],
          ['id' => 'herbivore', 'label' => 'Herbivore', 'count' => $counts['diet']['herbivore']],
          ['id' => 'omnivore',  'label' => 'Omnivore',  'count' => $counts['diet']['omnivore']],
      ];
      $statusChips = [
          ['id' => 'all',        'label' => 'Any status',     'count' => $counts['status']['all']],
          ['id' => 'stable',     'label' => 'Least concern',  'count' => $counts['status']['stable']],
          ['id' => 'endangered', 'label' => 'Endangered',     'count' => $counts['status']['endangered']],
      ];
      $groups = [
          ['key' => 'diet',    'label' => 'Diet',    'chips' => $dietChips,    'current' => $diet],
          ['key' => 'status',  'label' => 'Status',  'chips' => $statusChips,  'current' => $status],
          ['key' => 'habitat', 'label' => 'Habitat', 'chips' => $habitatChips, 'current' => $habitat],
      ];
      foreach ($groups as $g): ?>
        <div class="filter-group">
          <span class="glabel"><?= $g['label'] ?></span>
          <?php foreach ($g['chips'] as $opt): ?>
            <a class="chip<?= $g['current'] === $opt['id'] ? ' on' : '' ?>"
               href="<?= htmlspecialchars(chip_url($g['key'], $opt['id'])) ?>#catalog">
              <?= htmlspecialchars($opt['label']) ?>
              <span class="count"><?= number_format((int) $opt['count']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </form>
</section>

<!-- ─── CATALOG GRID ─────────────────────────────────────────────────── -->
<section class="frame" id="catalog">
  <div class="catalog-meta">
    <span>
      Showing <?= $start ?>–<?= $end ?> of <?= number_format($total) ?> · sorted by recency
    </span>
    <span class="right">
      <?php if ($q !== '' || $diet !== 'all' || $status !== 'all' || $habitat !== 'all'): ?>
        <a href="index.php#catalog">Clear filters</a>
      <?php endif; ?>
    </span>
  </div>

  <?php if (count($species) === 0): ?>
    <div class="empty-state">
      <div class="placeholder" style="height:240px">
        <div class="silhouette" aria-hidden="true"></div>
        <div>No specimens match this filter</div>
        <div style="font-family:var(--serif);font-style:italic;font-size:14px;color:var(--ink-soft);letter-spacing:0;text-transform:none;margin-top:6px">
          Try clearing a filter or broadening your search.
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($species as $s):
          $hasPhoto = !empty($s->image_url);
          $isEnd    = !empty($s->is_endangered);
          $dot      = conservation_dot($isEnd);
          $plate    = plate_num($s->_id);
      ?>
        <a class="card" href="species_detail.php?id=<?= urlencode((string) $s->_id) ?>" tabindex="0">
          <div class="card-img<?= $hasPhoto ? ' has-photo' : '' ?>">
            <?php if ($hasPhoto): ?>
              <div class="img" style="background-image:url('<?= htmlspecialchars($s->image_url) ?>')"></div>
            <?php else: ?>
              <div class="placeholder">
                <div class="silhouette" aria-hidden="true"></div>
                <div>Plate №<?= $plate ?></div>
                <div style="font-family:var(--serif);font-style:italic;font-size:13px;letter-spacing:0;text-transform:none;color:var(--ink-soft)">
                  <?= htmlspecialchars($s->scientific_name ?? '') ?>
                </div>
                <div style="margin-top:6px;color:var(--ink-mute)">Photograph forthcoming</div>
              </div>
            <?php endif; ?>
            <span class="num-tag">№<?= $plate ?></span>
            <span class="status-dot" data-s="<?= $dot ?>" title="<?= conservation_label($isEnd) ?>"></span>
          </div>
          <div>
            <h3 class="common"><?= htmlspecialchars($s->name ?? 'Unknown') ?></h3>
            <div class="lat-name"><?= htmlspecialchars($s->scientific_name ?? '') ?></div>
          </div>
          <div class="card-foot">
            <span class="status" data-s="<?= $dot ?>"><?= conservation_label($isEnd) ?></span>
            <span><?= htmlspecialchars($s->habitat_name ?? '—') ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <a class="page-prev" aria-disabled="<?= $page === 1 ? 'true' : 'false' ?>"
         href="<?= $page > 1 ? htmlspecialchars(page_url($page - 1)) . '#catalog' : '#catalog' ?>">
        ← Previous
      </a>
      <div class="page-nums">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a class="page-num<?= $i === $page ? ' on' : '' ?>"
             href="<?= htmlspecialchars(page_url($i)) ?>#catalog">
            <?= str_pad((string) $i, 2, '0', STR_PAD_LEFT) ?>
          </a>
        <?php endfor; ?>
      </div>
      <a class="page-next" aria-disabled="<?= $page === $totalPages ? 'true' : 'false' ?>"
         href="<?= $page < $totalPages ? htmlspecialchars(page_url($page + 1)) . '#catalog' : '#catalog' ?>">
        Next →
      </a>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
