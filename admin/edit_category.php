<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id  = Mongo::oid($_GET['id'] ?? null);
$cat = $id ? $db->findById('categories', $id) : null;

if (!$cat) {
    admin_layout_open('Category not found', 'categories');
    echo '<div class="alert error">Category not found.</div>';
    echo '<a href="manage_categories.php" class="btn btn-ghost" style="margin-top:16px">← Back</a>';
    admin_layout_close();
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        $db->update('categories', ['_id' => $id], ['name' => $name]);
        $db->update('species', ['category_id' => $id], ['category_name' => $name], true);
        log_activity($db, 'update', 'category', $name);
        header('Location: manage_categories.php');
        exit;
    }
}

admin_layout_open('Edit Category', 'categories');
?>

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Taxonomy · editing
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Edit <i style="color:var(--oriole-deep)"><?= htmlspecialchars($cat->name) ?>.</i>
    </h1>
    <p style="font-family:var(--serif);font-style:italic;font-size:16px;color:var(--ink-soft);margin:10px 0 0">
      Renaming will also update every species in this category.
    </p>
  </div>
  <a href="manage_categories.php" class="btn btn-ghost" style="align-self:flex-start">← Back</a>
</header>

<?php if ($error): ?>
  <div class="alert error" style="margin-bottom:24px"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="contribute" style="max-width:560px">
  <?= csrf_field() ?>
  <fieldset class="fset">
    <legend>
      <span class="num">§ 01</span>
      <h2>Category</h2>
      <span class="req">Required</span>
    </legend>
    <div class="frow">
      <label for="category_name">Category name</label>
      <input type="text" id="category_name" name="category_name" value="<?= htmlspecialchars($cat->name) ?>" required>
    </div>
  </fieldset>
  <div class="submit-row">
    <a href="manage_categories.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">Save changes <span class="arrow" aria-hidden="true"></span></button>
  </div>
</form>

<?php admin_layout_close(); ?>
