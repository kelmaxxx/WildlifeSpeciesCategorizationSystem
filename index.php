<?php
session_start();
require_once __DIR__ . '/mongo.php';

// ----- Inputs ---------------------------------------------------------------
$search  = isset($_GET['search']) ? trim($_GET['search']) : '';
$filters = $_GET['filter'] ?? [];
if (!is_array($filters)) $filters = [$filters];
$sort    = $_GET['sort']  ?? 'name';
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

// ----- Build MongoDB filter (public site shows only approved) ----------------
$mongoFilter = ['approval_status' => 'approved'];

if ($search !== '') {
    $regex = new MongoDB\BSON\Regex(preg_quote($search, '/'), 'i');
    $mongoFilter['$or'] = [
        ['name'            => $regex],
        ['scientific_name' => $regex],
        ['category_name'   => $regex],
        ['habitat_name'    => $regex],
    ];
}

$catFilters = array_intersect($filters, ['Carnivore', 'Herbivore', 'Omnivore']);
if ($catFilters) {
    $mongoFilter['category_name'] = ['$in' => array_values($catFilters)];
}

if (in_array('endangered', $filters, true)) {
    $mongoFilter['is_endangered'] = true;
}

$sortOrder = match ($sort) {
    'newest'     => ['_id' => -1],
    'endangered' => ['is_endangered' => -1, 'name' => 1],
    default      => ['name' => 1],
};

$total      = $db->count('species', $mongoFilter);
$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);

$species = $db->find('species', $mongoFilter, [
    'sort'  => $sortOrder,
    'skip'  => ($page - 1) * $perPage,
    'limit' => $perPage,
]);

// ----- Stats for hero (always full counts of approved set) -------------------
$approvedFilter  = ['approval_status' => 'approved'];
$totalSpecies    = $db->count('species', $approvedFilter);
$endangeredCount = $db->count('species', $approvedFilter + ['is_endangered' => true]);
$habitatCount    = $db->count('habitats');

function chip_checked(array $filters, string $value): string {
    return in_array($value, $filters, true) ? 'checked' : '';
}

function page_url(int $page, array $params): string {
    $params['page'] = $page;
    // Drop empty params for tidy URLs
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null && $v !== []);
    return 'index.php?' . http_build_query($params);
}

$baseParams = [
    'search' => $search,
    'filter' => $filters,
    'sort'   => $sort !== 'name' ? $sort : '',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title='Wildlife Explorer'; $css=['public']; include __DIR__ . '/partials/head.php'; ?>
</head>
<body>

<?php
$topbar_show_greeting = true;
$topbar_browse_href   = '#species';
include __DIR__ . '/partials/topbar.php';
?>

<section class="hero">
  <h1>Discover, classify and protect wildlife</h1>
  <p>A growing catalog of species, their habitats, and conservation status — powered by a modern document database.</p>
  <div class="stats">
    <div><strong><?= $totalSpecies ?></strong> Species</div>
    <div><strong><?= $endangeredCount ?></strong> Endangered</div>
    <div><strong><?= $habitatCount ?></strong> Habitats</div>
  </div>
</section>

<form class="controls" method="GET">
  <div class="search-input">
    <input type="text" name="search" placeholder="Search species, scientific name, habitat…"
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn">Search</button>
  </div>

  <label class="chip <?= chip_checked($filters, 'Carnivore') ?>">
    <input type="checkbox" name="filter[]" value="Carnivore" onchange="this.form.submit()" <?= chip_checked($filters, 'Carnivore') ?>>
    Carnivore
  </label>
  <label class="chip <?= chip_checked($filters, 'Herbivore') ?>">
    <input type="checkbox" name="filter[]" value="Herbivore" onchange="this.form.submit()" <?= chip_checked($filters, 'Herbivore') ?>>
    Herbivore
  </label>
  <label class="chip <?= chip_checked($filters, 'Omnivore') ?>">
    <input type="checkbox" name="filter[]" value="Omnivore" onchange="this.form.submit()" <?= chip_checked($filters, 'Omnivore') ?>>
    Omnivore
  </label>
  <label class="chip endangered <?= chip_checked($filters, 'endangered') ?>">
    <input type="checkbox" name="filter[]" value="endangered" onchange="this.form.submit()" <?= chip_checked($filters, 'endangered') ?>>
    Endangered only
  </label>

  <select name="sort" class="sort-select" onchange="this.form.submit()">
    <option value="name"       <?= $sort === 'name'       ? 'selected' : '' ?>>Sort: A → Z</option>
    <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Sort: Newest</option>
    <option value="endangered" <?= $sort === 'endangered' ? 'selected' : '' ?>>Sort: Endangered first</option>
  </select>

  <?php if ($search !== '' || $filters || $sort !== 'name'): ?>
    <a class="btn ghost" href="index.php">Reset</a>
  <?php endif; ?>
</form>

<div id="species" class="card-container">
  <?php if (count($species) === 0): ?>
    <div class="empty">
      <div class="empty-icon">&#128270;</div>
      <h3>No species match your filters</h3>
      <p>Try clearing your search or removing a filter.</p>
    </div>
  <?php else: foreach ($species as $s):
    $cat = strtolower($s->category_name ?? '');
    $img = $s->image_url ?? '';
    $sid = (string) $s->_id;
  ?>
    <a class="card-link" href="species_detail.php?id=<?= urlencode($sid) ?>">
      <article class="card">
        <?php if ($img): ?>
          <img class="card-img" src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($s->name) ?>">
        <?php else: ?>
          <div class="card-img">&#128062;</div>
        <?php endif; ?>
        <div class="card-content">
          <div class="badges">
            <?php if (!empty($s->category_name)): ?>
              <span class="badge <?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($s->category_name) ?></span>
            <?php endif; ?>
            <?php if (!empty($s->is_endangered)): ?>
              <span class="badge endangered">Endangered</span>
            <?php endif; ?>
          </div>
          <h3><?= htmlspecialchars($s->name ?? 'Unknown') ?></h3>
          <em><?= htmlspecialchars($s->scientific_name ?? '') ?></em>
          <p class="meta">
            <strong>Habitat:</strong> <?= htmlspecialchars($s->habitat_name ?? '—') ?>
            <?php if (!empty($s->habitat_location)): ?>
              <br><span style="color:var(--slate-500);font-size:.8rem">
                <?= htmlspecialchars($s->habitat_location) ?>
              </span>
            <?php endif; ?>
          </p>
        </div>
      </article>
    </a>
  <?php endforeach; endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="pagination" aria-label="pagination">
  <a class="page-link <?= $page === 1 ? 'disabled' : '' ?>"
     href="<?= page_url(max(1, $page - 1), $baseParams) ?>">&larr; Previous</a>
  <span class="page-info">Page <?= $page ?> of <?= $totalPages ?> · <?= $total ?> species</span>
  <a class="page-link <?= $page === $totalPages ? 'disabled' : '' ?>"
     href="<?= page_url(min($totalPages, $page + 1), $baseParams) ?>">Next &rarr;</a>
</nav>
<?php endif; ?>

</body>
</html>
