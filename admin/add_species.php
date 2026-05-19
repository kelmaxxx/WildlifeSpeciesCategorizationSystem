<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

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

        $db->insert('species', [
            'name'             => $name,
            'scientific_name'  => $sci,
            'is_endangered'    => $endangered,
            'image_url'        => $imgUrl,
            'category_id'      => $catId,
            'category_name'    => $cat->name ?? null,
            'habitat_id'       => $habId,
            'habitat_name'     => $hab->name ?? null,
            'habitat_location' => $hab->location ?? null,
            'approval_status'  => 'approved',
            'created_at'       => new MongoDB\BSON\UTCDateTime(),
        ]);
        log_activity($db, 'create', 'species', $name);

        header('Location: manage_species.php');
        exit;
    }
}

admin_layout_open('Add Species', 'species');
?>

<header class="page-header">
  <div>
    <h1>Add new species</h1>
    <p class="subtitle">Fill in the details below.</p>
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
      <input type="text" id="species_name" name="species_name" required>
    </div>
    <div class="form-row">
      <label for="scientific_name">Scientific name</label>
      <input type="text" id="scientific_name" name="scientific_name">
    </div>
    <div class="form-row">
      <label for="category_id">Category</label>
      <select id="category_id" name="category_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (string)$c->_id ?>"><?= htmlspecialchars($c->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <label for="habitat_id">Habitat</label>
      <select id="habitat_id" name="habitat_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($habitats as $h): ?>
          <option value="<?= (string)$h->_id ?>"><?= htmlspecialchars($h->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <label class="check"><input type="checkbox" name="is_endangered"> This species is endangered</label>
    </div>
    <div class="form-row">
      <label for="image_url">Image URL <span class="hint">optional</span></label>
      <input type="url" id="image_url" name="image_url" placeholder="https://…">
      <div class="image-preview" id="image_preview" hidden><img alt="preview"></div>
    </div>
    <div class="form-actions">
      <a href="manage_species.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Add species</button>
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
  update();
})();
</script>

<?php admin_layout_close();
