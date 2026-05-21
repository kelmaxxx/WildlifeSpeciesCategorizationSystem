<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$categories = $db->find('categories', [], ['sort' => ['name' => 1]]);
$habitats   = $db->find('habitats',   [], ['sort' => ['name' => 1]]);

$error = null;
$old = [
    'species_name'    => $_POST['species_name']    ?? '',
    'scientific_name' => $_POST['scientific_name'] ?? '',
    'category_id'     => $_POST['category_id']     ?? '',
    'habitat_id'      => $_POST['habitat_id']      ?? '',
    'image_url'       => $_POST['image_url']       ?? '',
    'is_endangered'   => isset($_POST['is_endangered']),
];

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

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Catalog · new entry
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Add <i style="color:var(--oriole-deep)">species.</i>
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
      <input type="text" id="species_name" name="species_name" value="<?= htmlspecialchars($old['species_name']) ?>" required>
    </div>

    <div class="frow">
      <label for="scientific_name">Scientific name <span class="opt">Optional</span></label>
      <input type="text" id="scientific_name" name="scientific_name" value="<?= htmlspecialchars($old['scientific_name']) ?>" placeholder="Genus species">
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
          <option value="<?= $cid ?>"<?= $old['category_id'] === $cid ? ' selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="frow">
      <label for="habitat_id">Habitat</label>
      <select id="habitat_id" name="habitat_id" required>
        <option value="">— Choose —</option>
        <?php foreach ($habitats as $h):
          $hid = (string) $h->_id; ?>
          <option value="<?= $hid ?>"<?= $old['habitat_id'] === $hid ? ' selected' : '' ?>><?= htmlspecialchars($h->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="frow">
      <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
        <input type="checkbox" name="is_endangered"<?= $old['is_endangered'] ? ' checked' : '' ?>>
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
      <input type="url" id="image_url" name="image_url" value="<?= htmlspecialchars($old['image_url']) ?>" placeholder="https://…">
    </div>
  </fieldset>

  <div class="submit-row">
    <a href="manage_species.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">
      Add species <span class="arrow" aria-hidden="true"></span>
    </button>
  </div>
</form>

<?php admin_layout_close(); ?>
