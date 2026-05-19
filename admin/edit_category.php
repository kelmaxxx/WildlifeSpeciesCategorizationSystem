<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$id  = Mongo::oid($_GET['id'] ?? null);
$cat = $id ? $db->findById('categories', $id) : null;

if (!$cat) {
    admin_layout_open('Category not found', 'categories');
    echo '<div class="alert error">Category not found.</div>';
    echo '<a href="manage_categories.php" class="btn">&larr; Back</a>';
    admin_layout_close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        $db->update('categories', ['_id' => $id], ['name' => $name]);
        // Keep denormalized name in species in sync
        $db->update('species', ['category_id' => $id], ['category_name' => $name], true);
        log_activity($db, 'update', 'category', $name);
        header('Location: manage_categories.php');
        exit;
    }
}

admin_layout_open('Edit Category', 'categories');
?>

<header class="page-header">
  <div>
    <h1>Edit category</h1>
    <p class="subtitle">Renaming will also update every species in this category.</p>
  </div>
  <a href="manage_categories.php" class="btn ghost">&larr; Back</a>
</header>

<div class="form-card">
  <?php if (!empty($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-row">
      <label for="category_name">Category name</label>
      <input type="text" id="category_name" name="category_name"
             value="<?= htmlspecialchars($cat->name) ?>" required>
    </div>
    <div class="form-actions">
      <a href="manage_categories.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Save changes</button>
    </div>
  </form>
</div>

<?php admin_layout_close();
