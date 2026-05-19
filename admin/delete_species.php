<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id      = Mongo::oid($_GET['id'] ?? $_POST['id'] ?? null);
$species = $id ? $db->findById('species', $id) : null;

if (!$species) {
    admin_layout_open('Species not found', 'species');
    echo '<div class="alert error">Species not found.</div>';
    echo '<a href="manage_species.php" class="btn">&larr; Back</a>';
    admin_layout_close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    csrf_check();
    $name = $species->name ?? '(unnamed)';
    $db->delete('species', ['_id' => $id]);
    log_activity($db, 'delete', 'species', $name);
    header('Location: manage_species.php');
    exit;
}

admin_layout_open('Delete Species', 'species');
?>
<div class="confirm-card">
  <div class="icon">&#9888;</div>
  <h2>Delete this species?</h2>
  <p>You're about to delete <strong><?= htmlspecialchars($species->name) ?></strong>.
     This action cannot be undone.</p>
  <form method="POST" class="confirm-actions">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id) ?>">
    <a href="manage_species.php" class="btn ghost">Cancel</a>
    <button type="submit" name="confirm" value="1" class="btn danger">Yes, delete</button>
  </form>
</div>
<?php admin_layout_close();
