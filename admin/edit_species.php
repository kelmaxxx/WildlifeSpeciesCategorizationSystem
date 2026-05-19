<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id      = Mongo::oid($_GET['id'] ?? null);
$species = $id ? $db->findById('species', $id) : null;

if (!$species) {
    admin_layout_open('Species not found', 'species');
    echo '<div class="alert error">Species not found.</div>';
    echo '<a href="manage_species.php" class="btn">&larr; Back to species</a>';
    admin_layout_close();
    exit;
}

$categories = $db->find('categories', [], ['sort' => ['name' => 1]]);
$habitats   = $db->find('habitats',   [], ['sort' => ['name' => 1]]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name      = trim($_POST['species_name'] ?? '');
    $sci       = trim($_POST['scientific_name'] ?? '');
    $catId     = Mongo::oid($_POST['category_id'] ?? null);
    $habId     = Mongo::oid($_POST['habitat_id']  ?? null);
    $endangered= isset($_POST['is_endangered']);
    $imgUrl    = trim($_POST['image_url'] ?? '');

    if ($name === '' || !$catId || !$habId) {
        $error = 'Name, category and habitat are required.';
    } elseif ($imgUrl !== '' && !preg_match('#^https?://#i', $imgUrl)) {
        $error = 'Image URL must start with http:// or https://.';
    } else {
        $cat = $db->findById('categories', $catId);
        $hab = $db->findById('habitats',   $habId);

        $db->update('species', ['_id' => $id], [
            'name'             => $name,
            'scientific_name'  => $sci,
            'is_endangered'    => $endangered,
            'image_url'        => $imgUrl,
            'category_id'      => $catId,
            'category_name'    => $cat->name ?? null,
            'habitat_id'       => $habId,
            'habitat_name'     => $hab->name ?? null,
            'habitat_location' => $hab->location ?? null,
        ]);
        log_activity($db, 'update', 'species', $name);

        header('Location: manage_species.php');
        exit;
    }
}

$selectedCat = isset($species->category_id) ? (string) $species->category_id : '';
$selectedHab = isset($species->habitat_id)  ? (string) $species->habitat_id  : '';

admin_layout_open('Edit Species', 'species');
?>

<header class="page-header">
  <div>
    <h1>Edit species</h1>
    <p class="subtitle">Update the details for this species.</p>
  </div>
  <a href="manage_species.php" class="btn ghost">&larr; Back</a>
</header>

<div class="form-card">
  <?php if (!empty($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-row">
      <label for="species_name">Species name</label>
      <input type="text" id="species_name" name="species_name"
             value="<?= htmlspecialchars($species->name ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="scientific_name">Scientific name</label>
      <input type="text" id="scientific_name" name="scientific_name"
             value="<?= htmlspecialchars($species->scientific_name ?? '') ?>">
    </div>
    <div class="form-row">
      <label for="category_id">Category</label>
      <select id="category_id" name="category_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($categories as $c):
          $cid = (string) $c->_id;
        ?>
          <option value="<?= $cid ?>" <?= $cid === $selectedCat ? 'selected' : '' ?>>
            <?= htmlspecialchars($c->name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <label for="habitat_id">Habitat</label>
      <select id="habitat_id" name="habitat_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($habitats as $h):
          $hid = (string) $h->_id;
        ?>
          <option value="<?= $hid ?>" <?= $hid === $selectedHab ? 'selected' : '' ?>>
            <?= htmlspecialchars($h->name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <label class="check">
        <input type="checkbox" name="is_endangered" <?= !empty($species->is_endangered) ? 'checked' : '' ?>>
        This species is endangered
      </label>
    </div>
    <div class="form-row">
      <label for="image_url">Image URL</label>
      <input type="url" id="image_url" name="image_url"
             value="<?= htmlspecialchars($species->image_url ?? '') ?>" placeholder="https://…">
      <div class="image-preview" id="image_preview" <?= empty($species->image_url) ? 'hidden' : '' ?>>
        <img alt="preview" src="<?= htmlspecialchars($species->image_url ?? '') ?>">
      </div>
    </div>
    <div class="form-actions">
      <a href="manage_species.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Save changes</button>
    </div>
  </form>
</div>

<script>
(function(){
  const input = document.getElementById('image_url');
  const box   = document.getElementById('image_preview');
  const img   = box.querySelector('img');
  function update() {
    const v = input.value.trim();
    if (!v) { box.hidden = true; return; }
    img.src = v;
    box.hidden = false;
  }
  input.addEventListener('input', update);
  img.addEventListener('error', () => { box.hidden = true; });
})();
</script>

<?php admin_layout_close();
