<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id  = Mongo::oid($_GET['id'] ?? null);
$hab = $id ? $db->findById('habitats', $id) : null;

if (!$hab) {
    admin_layout_open('Habitat not found', 'habitats');
    echo '<div class="alert error">Habitat not found.</div>';
    echo '<a href="manage_habitats.php" class="btn btn-ghost" style="margin-top:16px">← Back</a>';
    admin_layout_close();
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim($_POST['habitat_name'] ?? '');
    $location = trim($_POST['habitat_location'] ?? '');

    if ($name === '') {
        $error = 'Habitat name is required.';
    } else {
        $db->update('habitats', ['_id' => $id], ['name' => $name, 'location' => $location]);
        $db->update('species', ['habitat_id' => $id], ['habitat_name' => $name, 'habitat_location' => $location], true);
        log_activity($db, 'update', 'habitat', $name);
        header('Location: manage_habitats.php');
        exit;
    }
}

admin_layout_open('Edit Habitat', 'habitats');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Taxonomy · editing
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Edit <i style="color:var(--oriole-deep)"><?= htmlspecialchars($hab->name) ?>.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:16px;color:var(--ink-soft);margin:10px 0 0">
      Renaming will also update every species in this habitat.
    </p>
  </div>
  <a href="manage_habitats.php" class="btn btn-ghost" style="align-self:flex-start">← Back</a>
</header>

<?php if ($error): ?>
  <div class="alert error" style="margin-bottom:24px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="contribute" style="max-width:560px">
  <?= csrf_field() ?>
  <fieldset class="fset">
    <legend>
      <span class="num">§ 01</span>
      <h2>Habitat</h2>
      <span class="req">Required</span>
    </legend>
    <div class="frow">
      <label for="habitat_name">Habitat name</label>
      <input type="text" id="habitat_name" name="habitat_name" value="<?= htmlspecialchars($hab->name) ?>" required>
    </div>
    <div class="frow">
      <label for="habitat_location">Region <span class="opt">Optional</span></label>
      <input type="text" id="habitat_location" name="habitat_location" value="<?= htmlspecialchars($hab->location ?? '') ?>">
    </div>
  </fieldset>
  <div class="submit-row">
    <a href="manage_habitats.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">Save changes <span class="arrow" aria-hidden="true"></span></button>
  </div>
</form>

<?php admin_layout_close(); ?>
