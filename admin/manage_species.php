<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/settings.php';

$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');
$featuredId   = get_setting($db, 'featured_species_id');
$featuredKey  = $featuredId ? (string) $featuredId : '';

$filter = [];
if (in_array($statusFilter, ['pending','approved','rejected'], true)) {
    $filter['approval_status'] = $statusFilter;
}
if ($search !== '') {
    $regex = new MongoDB\BSON\Regex(preg_quote($search, '/'), 'i');
    $filter['$or'] = [
        ['name'            => $regex],
        ['scientific_name' => $regex],
        ['category_name'   => $regex],
        ['habitat_name'    => $regex],
    ];
}

$species = $db->find('species', $filter, ['sort' => ['name' => 1]]);

admin_layout_open('Manage Species', 'species');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Catalog · <?= count($species) ?> records
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:44px;line-height:1.05;letter-spacing:-.018em;margin:8px 0 0;color:var(--ink);font-weight:500">
      All <i style="color:var(--forest-deep)">species.</i>
    </h1>
  </div>
  <a href="add_species.php" class="btn btn-primary" style="align-self:flex-start">
    Add new species <span class="arrow" aria-hidden="true"></span>
  </a>
</header>

<section class="panel" style="border-right:0;padding:32px 0">
  <form class="panel-head" method="GET" style="gap:16px;flex-wrap:wrap">
    <h2 style="font-family:var(--serif);font-size:28px;letter-spacing:-.01em;margin:0;color:var(--ink)">Records.</h2>
    <div class="tools" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <input type="search" name="q" placeholder="Search…" value="<?= htmlspecialchars($search) ?>"
             style="font-family:var(--sans);font-size:13px;padding:8px 12px;border:1px solid var(--rule);border-radius:6px;background:var(--paper);color:var(--ink);min-width:200px">
      <select name="status" onchange="this.form.submit()"
              style="font-family:var(--sans);font-size:13px;padding:8px 12px;border:1px solid var(--rule);border-radius:6px;background:var(--paper);color:var(--ink)">
        <option value="">All statuses</option>
        <?php foreach (['pending','approved','rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-ghost">Apply</button>
      <?php if ($search !== '' || $statusFilter !== ''): ?>
        <a href="manage_species.php" class="btn btn-ghost">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if (count($species) === 0): ?>
    <div style="padding:48px 0;text-align:center;font-family:var(--serif);font-style:italic;color:var(--ink-soft)">
      No species match your filters.
    </div>
  <?php else: ?>
    <?php
      $backUrl = 'manage_species.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
    ?>
    <table class="tbl">
      <thead>
        <tr>
          <th>Species</th>
          <th>Diet</th>
          <th>Habitat</th>
          <th>Status</th>
          <th>Featured</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($species as $s):
          $sid       = (string) $s->_id;
          $status    = $s->approval_status ?? 'approved';
          $img       = $s->image_url ?? '';
          $isEnd     = !empty($s->is_endangered);
          $isFeat    = ($sid === $featuredKey);
          $canFeat   = ($status === 'approved');
        ?>
          <tr<?= $isFeat ? ' class="is-featured"' : '' ?>>
            <td>
              <div class="spec">
                <div class="thumb">
                  <?php if ($img): ?>
                    <div class="img" style="background-image:url('<?= htmlspecialchars($img) ?>')"></div>
                  <?php endif; ?>
                </div>
                <div class="name">
                  <a class="common" href="edit_species.php?id=<?= urlencode($sid) ?>" style="color:inherit;text-decoration:none">
                    <?= htmlspecialchars($s->name ?? 'Unknown') ?>
                  </a>
                  <div class="latin"><?= htmlspecialchars($s->scientific_name ?? '') ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
            <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
            <td>
              <span class="status" data-s="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
              <?php if ($isEnd): ?>
                <span style="font-family:var(--mono);font-size:10px;color:var(--status-end);margin-left:6px;text-transform:uppercase;letter-spacing:.08em">· at risk</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($isFeat): ?>
                <form method="POST" action="set_featured.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="clear">
                  <input type="hidden" name="back" value="<?= htmlspecialchars($backUrl) ?>">
                  <button type="submit"
                          title="Unfeature this species"
                          style="font-family:var(--mono);font-size:10px;color:var(--forest-deep);text-transform:uppercase;letter-spacing:.12em;padding:5px 10px;border:1px solid var(--forest);background:var(--mint);border-radius:6px;cursor:pointer">
                    ★ Featured · unset
                  </button>
                </form>
              <?php elseif ($canFeat): ?>
                <form method="POST" action="set_featured.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="set">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($sid) ?>">
                  <input type="hidden" name="back" value="<?= htmlspecialchars($backUrl) ?>">
                  <button type="submit"
                          title="Show this species in the homepage feature section"
                          style="font-family:var(--mono);font-size:10px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.12em;padding:5px 10px;border:1px solid var(--rule);background:transparent;border-radius:6px;cursor:pointer">
                    ☆ Set featured
                  </button>
                </form>
              <?php else: ?>
                <span style="font-family:var(--mono);font-size:10px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.12em">—</span>
              <?php endif; ?>
            </td>
            <td style="text-align:right">
              <div style="display:inline-flex;gap:6px">
                <a href="edit_species.php?id=<?= urlencode($sid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--rule-soft);border-radius:6px;text-decoration:none">Edit</a>
                <a href="delete_species.php?id=<?= urlencode($sid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:#8b2c2c;text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid #8b2c2c;border-radius:6px;text-decoration:none">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="margin-top:18px;font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-mute)">
      The featured species appears in the "Featured species" block on the homepage.
      Only approved species can be featured. Set none to fall back to the most recent endangered species.
    </p>
  <?php endif; ?>
</section>

<?php admin_layout_close(); ?>
