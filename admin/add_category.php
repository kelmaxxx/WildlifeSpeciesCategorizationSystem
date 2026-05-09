<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        $existing = $db->findOne('categories', ['name' => $name]);
        if ($existing) {
            $error = 'A category with that name already exists.';
        } else {
            $db->insert('categories', ['name' => $name]);
            log_activity($db, 'create', 'category', $name);
            header('Location: manage_categories.php');
            exit;
        }
    }
}

admin_layout_open('Add Category', 'categories');
?>

<header class="page-header">
  <div>
    <h1>Add new category</h1>
    <p class="subtitle">Create a new diet/grouping option.</p>
  </div>
  <a href="manage_categories.php" class="btn ghost">&larr; Back</a>
</header>

<div class="form-card">
  <?php if (!empty($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-row">
      <label for="category_name">Category name</label>
      <input type="text" id="category_name" name="category_name" required autofocus>
    </div>
    <div class="form-actions">
      <a href="manage_categories.php" class="btn ghost">Cancel</a>
      <button type="submit" class="btn">Add category</button>
    </div>
  </form>
</div>

<?php admin_layout_close();
