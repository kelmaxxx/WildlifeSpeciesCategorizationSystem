<?php
require_once __DIR__ . '/auth.php';

$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

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
    <table class="tbl">
      <thead>
        <tr>
          <th>Species</th>
          <th>Diet</th>
          <th>Habitat</th>
          <th>Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($species as $s):
          $sid    = (string) $s->_id;
          $status = $s->approval_status ?? 'approved';
          $img    = $s->image_url ?? '';
          $isEnd  = !empty($s->is_endangered);
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
            <td style="text-align:right">
              <div style="display:inline-flex;gap:6px">
                <a href="edit_species.php?id=<?= urlencode($sid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--rule-soft);border-radius:6px;text-decoration:none">Edit</a>
                <a href="delete_species.php?id=<?= urlencode($sid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:var(--berry);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--berry);border-radius:6px;text-decoration:none">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php admin_layout_close(); ?>
