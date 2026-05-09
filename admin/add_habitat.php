<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

<header class="page-header">
  <div>
    <h1>Add new habitat</h1>
    <p class="subtitle">Add a new biome or geographic region.</p>
  </div>
  <a href="manage_habitats.php" class="btn ghost">&larr; Back</a>
</header>

<div class="form-card">
  <?php if (!empty($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-row">
      <label for="habitat_name">Habitat name</label>
      <input type="text" id="habitat_name" name="habitat_name" required autofocus>
    </div>
    <div class="form-row">
      <label for="habitat_location">Location <span style="font-weight:400;color:var(--slate-500)">(optional)</span></label>
      <input type="text" id="habitat_location" name="habitat_location" placeholder="e.g. Amazon, Congo, Southeast Asia">
    </div>
    <div class="form-actions">
      <a href="manage_habitats.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Add habitat</button>
    </div>
  </form>
</div>

<?php admin_layout_close();
