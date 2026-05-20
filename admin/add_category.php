<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
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

<header class="admin-top">
  <div>
    <div class="eyebrow" style="font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--ink-mute)">
      Taxonomy · new entry
    </div>
    <h1 class="display" style="font-family:var(--serif);font-size:48px;line-height:1;letter-spacing:-.015em;margin:8px 0 0;color:var(--ink)">
      Add <i style="color:var(--oriole-deep)">category.</i>
    </h1>
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
      <input type="text" id="category_name" name="category_name" required autofocus placeholder="e.g. Carnivore">
    </div>
  </fieldset>
  <div class="submit-row">
    <a href="manage_categories.php" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">Add category <span class="arrow" aria-hidden="true"></span></button>
  </div>
</form>

<?php admin_layout_close(); ?>
