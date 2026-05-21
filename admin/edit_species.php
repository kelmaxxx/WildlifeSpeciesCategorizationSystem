<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id      = Mongo::oid($_GET['id'] ?? null);
$species = $id ? $db->findById('species', $id) : null;

if (!$species) {
    admin_layout_open('Species not found', 'species');
    echo '<div class="alert error">Species not found.</div>';
    echo '<a href="manage_species.php" class="btn btn-ghost" style="margin-top:16px">← Back to species</a>';
    admin_layout_close();
    exit;
}

$categories = $db->find('categories', [], ['sort' => ['name' => 1]]);
$habitats   = $db->find('habitats',   [], ['sort' => ['name' => 1]]);
$error = null;

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

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Catalog · Edit record
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:44px;line-height:1.05;letter-spacing:-.018em;margin:8px 0 0;color:var(--ink);font-weight:500">
      Edit <i style="color:var(--forest-deep)"><?= htmlspecialchars($species->name ?? 'species') ?>.</i>
    </h1>
  </div>
  <a href="manage_species.php" class="btn btn-ghost" style="align-self:flex-start">← Back to species</a>
</header>

<?php if ($error): ?>
  <div class="alert error" style="margin-bottom:24px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="contribute" style="max-width:760px">
  <?= csrf_field() ?>

  <fieldset class="fset">
    <legend>
      <span class="num">§ 01</span>
      <h2>Identification</h2>
      <span class="req">Required</span>
    </legend>

    <div class="frow">
      <label for="species_name">Species name</label>
      <input type="text" id="species_name" name="species_name" value="<?= htmlspecialchars($species->name ?? '') ?>" required>
    </div>

    <div class="frow">
      <label for="scientific_name">Scientific name</label>
      <input type="text" id="scientific_name" name="scientific_name" value="<?= htmlspecialchars($species->scientific_name ?? '') ?>" placeholder="Genus species">
    </div>
  </fieldset>

  <fieldset class="fset">
    <legend>
      <span class="num">§ 02</span>
      <h2>Classification</h2>
      <span class="req">Required</span>
    </legend>

    <div class="frow">
      <label for="category_id">Category</label>
      <select id="category_id" name="category_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($categories as $c):
          $cid = (string) $c->_id; ?>
          <option value="<?= $cid ?>"<?= $cid === $selectedCat ? ' selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="frow">
      <label for="habitat_id">Habitat</label>
      <select id="habitat_id" name="habitat_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($habitats as $h):
          $hid = (string) $h->_id; ?>
          <option value="<?= $hid ?>"<?= $hid === $selectedHab ? ' selected' : '' ?>><?= htmlspecialchars($h->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="frow">
      <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
        <input type="checkbox" name="is_endangered"<?= !empty($species->is_endangered) ? ' checked' : '' ?>>
        <span>This species is endangered</span>
      </label>
    </div>
  </fieldset>

  <fieldset class="fset">
    <legend>
      <span class="num">§ 03</span>
      <h2>Photograph</h2>
      <span class="req">Optional</span>
    </legend>

    <div class="frow">
      <label for="image_url">Image URL</label>
      <input type="url" id="image_url" name="image_url" value="<?= htmlspecialchars($species->image_url ?? '') ?>" placeholder="https://…">
    </div>
  </fieldset>

  <div class="submit-row">
    <a href="manage_species.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">
      Save changes <span class="arrow" aria-hidden="true"></span>
    </button>
  </div>
</form>

<?php admin_layout_close(); ?>
