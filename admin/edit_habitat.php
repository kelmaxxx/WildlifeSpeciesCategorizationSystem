<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id  = Mongo::oid($_GET['id'] ?? null);
$hab = $id ? $db->findById('habitats', $id) : null;

if (!$hab) {
    admin_layout_open('Habitat not found', 'habitats');
    echo '<div class="alert error">Habitat not found.</div>';
    echo '<a href="manage_habitats.php" class="btn">&larr; Back</a>';
    admin_layout_close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim($_POST['habitat_name'] ?? '');
    $location = trim($_POST['habitat_location'] ?? '');

    if ($name === '') {
        $error = 'Habitat name is required.';
    } else {
        $db->update('habitats', ['_id' => $id], [
            'name'     => $name,
            'location' => $location,
        ]);
        // Sync denormalized fields on every species in this habitat
        $db->update('species', ['habitat_id' => $id], [
            'habitat_name'     => $name,
            'habitat_location' => $location,
        ], true);
        log_activity($db, 'update', 'habitat', $name);
        header('Location: manage_habitats.php');
        exit;
    }
}

admin_layout_open('Edit Habitat', 'habitats');
?>

<header class="page-header">
  <div>
    <h1>Edit habitat</h1>
    <p class="subtitle">Renaming will also update every species in this habitat.</p>
  </div>
  <a href="manage_habitats.php" class="btn ghost">&larr; Back</a>
</header>

<div class="form-card">
  <?php if (!empty($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-row">
      <label for="habitat_name">Habitat name</label>
      <input type="text" id="habitat_name" name="habitat_name"
             value="<?= htmlspecialchars($hab->name) ?>" required>
    </div>
    <div class="form-row">
      <label for="habitat_location">Location</label>
      <input type="text" id="habitat_location" name="habitat_location"
             value="<?= htmlspecialchars($hab->location ?? '') ?>">
    </div>
    <div class="form-actions">
      <a href="manage_habitats.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Save changes</button>
    </div>
  </form>
</div>

<?php admin_layout_close();
