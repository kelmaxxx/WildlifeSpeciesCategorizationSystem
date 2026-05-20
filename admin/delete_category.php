<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id  = Mongo::oid($_GET['id'] ?? $_POST['id'] ?? null);
$cat = $id ? $db->findById('categories', $id) : null;

if (!$cat) {
    admin_layout_open('Category not found', 'categories');
    echo '<div class="alert error">Category not found.</div>';
    echo '<a href="manage_categories.php" class="btn btn-ghost" style="margin-top:16px">← Back</a>';
    admin_layout_close();
    exit;
}

$speciesUsing = $db->count('species', ['category_id' => $id]);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    csrf_check();
    if ($speciesUsing > 0) {
        $error = "Cannot delete: $speciesUsing species are still using this category.";
    } else {
        $db->delete('categories', ['_id' => $id]);
        log_activity($db, 'delete', 'category', $cat->name);
        header('Location: manage_categories.php');
        exit;
    }
}

admin_layout_open('Delete Category', 'categories');
?>

<div style="max-width:560px;margin:48px auto;padding:48px 40px;background:var(--cream);border:1px solid var(--rule-soft);border-radius:14px;text-align:center">
  <div style="font-family:var(--mono);font-size:11px;color:var(--berry);text-transform:uppercase;letter-spacing:.14em;margin-bottom:18px">
    § Destructive action
  </div>
  <h1 style="font-family:var(--serif);font-size:36px;line-height:1.1;margin:0 0 14px;color:var(--ink)">
    Delete this <i style="color:var(--berry)">category?</i>
  </h1>
  <p style="font-family:var(--serif);font-style:italic;font-size:17px;color:var(--ink-soft);margin:0 0 16px">
    You're about to delete <b style="color:var(--ink);font-style:normal"><?= htmlspecialchars($cat->name) ?></b>.
  </p>

  <?php if ($error): ?>
    <div class="alert error" style="text-align:left;margin-bottom:24px"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($speciesUsing > 0): ?>
    <div class="alert warn" style="text-align:left;margin-bottom:24px">
      <?= $speciesUsing ?> species currently use this category. Reassign or delete those first.
    </div>
  <?php endif; ?>

  <form method="POST" style="display:flex;justify-content:center;gap:12px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $id) ?>">
    <a href="manage_categories.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" name="confirm" value="1"
            <?= $speciesUsing > 0 ? 'disabled' : '' ?>
            style="font-family:var(--mono);font-size:13px;text-transform:uppercase;letter-spacing:.1em;padding:10px 20px;border:0;border-radius:6px;background:var(--berry);color:#fff;cursor:<?= $speciesUsing > 0 ? 'not-allowed' : 'pointer' ?>;opacity:<?= $speciesUsing > 0 ? '.5' : '1' ?>">
      Yes, delete
    </button>
  </form>
</div>

<?php admin_layout_close(); ?>
