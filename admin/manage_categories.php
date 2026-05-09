<?php
require_once __DIR__ . '/auth.php';

$categories = $db->find('categories', [], ['sort' => ['name' => 1]]);

admin_layout_open('Manage Categories', 'categories');
?>

<header class="page-header">
  <div>
    <h1>Categories</h1>
    <p class="subtitle">Carnivore, herbivore, omnivore — and any custom group you add.</p>
  </div>
  <a href="add_category.php" class="btn">&#43; Add new category</a>
</header>

<div class="panel">
  <table class="table">
    <thead>
      <tr><th>Name</th><th>Species count</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (count($categories) === 0): ?>
        <tr><td colspan="3" style="text-align:center;color:var(--slate-500);padding:2rem">No categories yet.</td></tr>
      <?php else: foreach ($categories as $c):
        $cid   = (string) $c->_id;
        $count = $db->count('species', ['category_id' => $c->_id]);
      ?>
        <tr>
          <td><strong><?= htmlspecialchars($c->name) ?></strong></td>
          <td><?= $count ?></td>
          <td>
            <div class="actions">
              <a class="edit" href="edit_category.php?id=<?= urlencode($cid) ?>">Edit</a>
              <a class="del"  href="delete_category.php?id=<?= urlencode($cid) ?>">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php admin_layout_close();
