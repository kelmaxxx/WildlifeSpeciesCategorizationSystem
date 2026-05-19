<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id  = Mongo::oid($_GET['id'] ?? $_POST['id'] ?? null);
$hab = $id ? $db->findById('habitats', $id) : null;

if (!$hab) {
    admin_layout_open('Habitat not found', 'habitats');
    echo '<div class="alert error">Habitat not found.</div>';
    echo '<a href="manage_habitats.php" class="btn">&larr; Back</a>';
    admin_layout_close();
    exit;
}

$speciesUsing = $db->count('species', ['habitat_id' => $id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    csrf_check();
    if ($speciesUsing > 0) {
        $error = "Cannot delete: $speciesUsing species are still using this habitat.";
    } else {
        $db->delete('habitats', ['_id' => $id]);
        log_activity($db, 'delete', 'habitat', $hab->name);
        header('Location: manage_habitats.php');
        exit;
    }
}

admin_layout_open('Delete Habitat', 'habitats');
?>
<div class="confirm-card">
  <div class="icon">&#9888;</div>
  <h2>Delete this habitat?</h2>
  <p>You're about to delete <strong><?= htmlspecialchars($hab->name) ?></strong>.</p>

  <?php if (!empty($error)): ?>
    <div class="alert error" style="text-align:left"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($speciesUsing > 0): ?>
    <div class="alert error" style="text-align:left">
      <?= $speciesUsing ?> species currently use this habitat. Reassign or delete those first.
    </div>
  <?php endif; ?>

  <form method="POST" class="confirm-actions">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id) ?>">
    <a href="manage_habitats.php" class="btn ghost">Cancel</a>
    <button type="submit" name="confirm" value="1" class="btn danger" <?= $speciesUsing > 0 ? 'disabled style="opacity:.6;cursor:not-allowed"' : '' ?>>
      Yes, delete
    </button>
  </form>
</div>
<?php admin_layout_close();
