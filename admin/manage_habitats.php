<?php
require_once __DIR__ . '/auth.php';

$habitats = $db->find('habitats', [], ['sort' => ['name' => 1]]);

admin_layout_open('Manage Habitats', 'habitats');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Taxonomy · <?= count($habitats) ?> entries
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      <i style="color:var(--oriole-deep)">Habitats.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:17px;color:var(--ink-soft);margin:12px 0 0;max-width:620px">
      Geographic and biome groupings — the ecosystems each species calls home.
    </p>
  </div>
  <a href="add_habitat.php" class="btn btn-primary" style="align-self:flex-start">
    Add habitat <span class="arrow" aria-hidden="true"></span>
  </a>
</header>

<section class="panel" style="border-right:0;padding:32px 0">
  <div class="panel-head">
    <h2 style="font-family:var(--serif);font-size:28px;letter-spacing:-.01em;margin:0;color:var(--ink)">Records.</h2>
  </div>

  <?php if (count($habitats) === 0): ?>
    <div style="padding:48px 0;text-align:center;font-family:var(--serif);font-style:italic;color:var(--ink-soft)">
      No habitats yet. <a href="add_habitat.php" style="color:var(--ink)">Add the first one</a>.
    </div>
  <?php else: ?>
    <table class="tbl">
      <thead>
        <tr>
          <th>Habitat</th>
          <th>Region</th>
          <th>Species in catalog</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($habitats as $h):
          $hid   = (string) $h->_id;
          $count = $db->count('species', ['habitat_id' => $h->_id]);
        ?>
          <tr>
            <td>
              <div class="name">
                <span class="common"><?= htmlspecialchars($h->name) ?></span>
              </div>
            </td>
            <td><?= htmlspecialchars($h->location ?? '—') ?></td>
            <td class="when"><?= number_format($count) ?></td>
            <td style="text-align:right">
              <div style="display:inline-flex;gap:6px">
                <a href="edit_habitat.php?id=<?= urlencode($hid) ?>"
                   style="font-family:var(--mono);font-size:11px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em;padding:6px 10px;border:1px solid var(--rule-soft);border-radius:6px;text-decoration:none">Edit</a>
                <a href="delete_habitat.php?id=<?= urlencode($hid) ?>"
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
