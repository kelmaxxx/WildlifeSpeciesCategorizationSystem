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

<header class="page-header">
  <div>
    <h1>Species</h1>
    <p class="subtitle">All species in the catalog. Filter by approval status or search by name.</p>
  </div>
  <a href="add_species.php" class="btn">&#43; Add new species</a>
</header>

<div class="panel">
  <div class="panel-header">
    <h2>All species (<?= count($species) ?>)</h2>
    <form class="panel-tools" method="GET">
      <input type="search" name="q" placeholder="Search…" value="<?= htmlspecialchars($search) ?>">
      <select name="status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <?php foreach (['pending','approved','rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn ghost">Apply</button>
      <?php if ($search !== '' || $statusFilter !== ''): ?>
        <a href="manage_species.php" class="btn ghost">Reset</a>
      <?php endif; ?>
    </form>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Scientific name</th>
        <th>Category</th>
        <th>Habitat</th>
        <th>Endangered</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($species) === 0): ?>
        <tr><td colspan="7" class="table-empty">No species match your filters.</td></tr>
      <?php else: foreach ($species as $s):
        $id     = (string) $s->_id;
        $status = $s->approval_status ?? 'approved';
      ?>
        <tr>
          <td><strong><?= htmlspecialchars($s->name ?? '') ?></strong></td>
          <td><em><?= htmlspecialchars($s->scientific_name ?? '') ?></em></td>
          <td><?= htmlspecialchars($s->category_name ?? '—') ?></td>
          <td><?= htmlspecialchars($s->habitat_name ?? '—') ?></td>
          <td><?= !empty($s->is_endangered) ? '<span class="badge endangered">Yes</span>' : 'No' ?></td>
          <td><span class="badge status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
          <td>
            <div class="actions">
              <a class="edit" href="edit_species.php?id=<?= urlencode($id) ?>">Edit</a>
              <a class="del"  href="delete_species.php?id=<?= urlencode($id) ?>">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php admin_layout_close();
