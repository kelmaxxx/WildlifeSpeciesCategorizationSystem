<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim($_POST['habitat_name'] ?? '');
    $location = trim($_POST['habitat_location'] ?? '');

    if ($name === '') {
        $error = 'Habitat name is required.';
    } else {
        $db->insert('habitats', ['name' => $name, 'location' => $location]);
        log_activity($db, 'create', 'habitat', $name);
        header('Location: manage_habitats.php');
        exit;
    }
}

admin_layout_open('Add Habitat', 'habitats');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Taxonomy · new entry
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Add <i style="color:var(--oriole-deep)">habitat.</i>
    </h1>
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
      <input type="text" id="habitat_name" name="habitat_name" required autofocus placeholder="e.g. Tropical Rainforest">
    </div>
    <div class="frow">
      <label for="habitat_location">Region <span class="opt">Optional</span></label>
      <input type="text" id="habitat_location" name="habitat_location" placeholder="e.g. Amazon, Congo, Southeast Asia">
    </div>
  </fieldset>
  <div class="submit-row">
    <a href="manage_habitats.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">Add habitat <span class="arrow" aria-hidden="true"></span></button>
  </div>
</form>

<?php admin_layout_close(); ?>
