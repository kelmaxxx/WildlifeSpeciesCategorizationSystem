<?php
require_once __DIR__ . '/auth.php';

$categories = $db->find('categories', [], ['sort' => ['name' => 1]]);

admin_layout_open('Manage Categories', 'categories');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Taxonomy · <?= count($categories) ?> entries
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      <i style="color:var(--oriole-deep)">Categories.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:17px;color:var(--ink-soft);margin:12px 0 0;max-width:620px">
      Carnivore, herbivore, omnivore — and any custom group you add to organise the catalog.
    </p>
  </div>
  <a href="add_category.php" class="btn btn-primary" style="align-self:flex-start">
    Add category <span class="arrow" aria-hidden="true"></span>
  </a>
</header>

<section class="panel" style="border-right:0;padding:32px 0">
  <div class="panel-head">
    <h2 style="font-family:var(--serif);font-size:28px;letter-spacing:-.01em;margin:0;color:var(--ink)">Records.</h2>
  </div>

  <?php if (count($categories) === 0): ?>
    <div style="padding:48px 0;text-align:center;font-family:var(--serif);font-style:italic;color:var(--ink-soft)">
      No categories yet. <a href="add_category.php" style="color:var(--ink)">Add the first one</a>.
    </div>
  <?php else: ?>
    <table class="tbl">
      <thead>
        <tr>
          <th>Category</th>
          <th>Species in catalog</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $c):
          $cid   = (string) $c->_id;
          $count = $db->count('species', ['category_id' => $c->_id]);
        ?>
          <tr>
            <td>
              <div class="name">
                <span class="common"><?= htmlspecialchars($c->name) ?></span>
              </div>
            </td>
            <td class="when"><?= number_format($count) ?></td>
            <td style="text-align:right">
              <div style="display:inline-flex;gap:6px">
                <a href="edit_category.php?id=<?= urlencode($cid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--rule-soft);border-radius:6px;text-decoration:none">Edit</a>
                <a href="delete_category.php?id=<?= urlencode($cid) ?>"
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
