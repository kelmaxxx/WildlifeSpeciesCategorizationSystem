<?php
require_once __DIR__ . '/auth.php';

$habitats = $db->find('habitats', [], ['sort' => ['name' => 1]]);

admin_layout_open('Manage Habitats', 'habitats');
?>

<header class="page-header">
  <div>
    <h1>Habitats</h1>
    <p class="subtitle">Geographic and biome groupings for your species.</p>
  </div>
  <a href="add_habitat.php" class="btn">&#43; Add new habitat</a>
</header>

<div class="panel">
  <table class="table">
    <thead>
      <tr><th>Name</th><th>Location</th><th>Species count</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (count($habitats) === 0): ?>
        <tr><td colspan="4" style="text-align:center;color:var(--slate-500);padding:2rem">No habitats yet.</td></tr>
      <?php else: foreach ($habitats as $h):
        $hid   = (string) $h->_id;
        $count = $db->count('species', ['habitat_id' => $h->_id]);
      ?>
        <tr>
          <td><strong><?= htmlspecialchars($h->name) ?></strong></td>
          <td><?= htmlspecialchars($h->location ?? '') ?></td>
          <td><?= $count ?></td>
          <td>
            <div class="actions">
              <a class="edit" href="edit_habitat.php?id=<?= urlencode($hid) ?>">Edit</a>
              <a class="del"  href="delete_habitat.php?id=<?= urlencode($hid) ?>">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php admin_layout_close();
