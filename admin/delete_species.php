<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id      = Mongo::oid($_GET['id'] ?? $_POST['id'] ?? null);
$species = $id ? $db->findById('species', $id) : null;

if (!$species) {
    admin_layout_open('Species not found', 'species');
    echo '<div class="alert error">Species not found.</div>';
    echo '<a href="manage_species.php" class="btn btn-ghost" style="margin-top:16px">← Back</a>';
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

<div style="max-width:560px;margin:48px auto;padding:48px 40px;background:var(--cream);border:1px solid var(--rule-soft);border-radius:14px;text-align:center">
  <div style="font-family:var(--mono);font-size:11px;color:var(--berry);text-transform:uppercase;letter-spacing:.14em;margin-bottom:18px">
    § Destructive action
  </div>
  <h1 style="font-family:var(--serif);font-size:36px;line-height:1.1;margin:0 0 14px;color:var(--ink)">
    Delete this <i style="color:var(--berry)">specimen?</i>
  </h1>
  <p style="font-family:var(--serif);font-style:italic;font-size:17px;color:var(--ink-soft);margin:0 0 8px">
    You're about to delete <b style="color:var(--ink);font-style:normal"><?= htmlspecialchars($species->name) ?></b>
    <?php if (!empty($species->scientific_name)): ?>
      (<i><?= htmlspecialchars($species->scientific_name) ?></i>)
    <?php endif; ?>.
  </p>
  <p style="font-family:var(--mono);font-size:12px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em;margin:0 0 32px">
    This action cannot be undone.
  </p>
  <form method="POST" style="display:flex;justify-content:center;gap:12px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $id) ?>">
    <a href="manage_species.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" name="confirm" value="1"
            style="font-family:var(--mono);font-size:13px;text-transform:uppercase;letter-spacing:.1em;padding:10px 20px;border:0;border-radius:6px;background:var(--berry);color:#fff;cursor:pointer">
      Yes, delete
    </button>
  </form>
</div>

<?php admin_layout_close(); ?>
