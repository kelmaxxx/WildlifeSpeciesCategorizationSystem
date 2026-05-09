<?php
session_start();
require_once __DIR__ . '/mongo.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$categories = $db->find('categories', [], ['sort' => ['name' => 1]]);
$habitats   = $db->find('habitats',   [], ['sort' => ['name' => 1]]);

$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['species_name'] ?? '');
    $sci        = trim($_POST['scientific_name'] ?? '');
    $catId      = Mongo::oid($_POST['category_id'] ?? null);
    $habId      = Mongo::oid($_POST['habitat_id']  ?? null);
    $endangered = isset($_POST['is_endangered']);
    $imgUrl     = trim($_POST['image_url'] ?? '');

    if ($name === '' || !$catId || !$habId) {
        $error = 'Name, category and habitat are required.';
    } else {
        $cat = $db->findById('categories', $catId);
        $hab = $db->findById('habitats',   $habId);

        $db->insert('species', [
            'name'             => $name,
            'scientific_name'  => $sci,
            'is_endangered'    => $endangered,
            'image_url'        => $imgUrl,
            'category_id'      => $catId,
            'category_name'    => $cat->name ?? null,
            'habitat_id'       => $habId,
            'habitat_name'     => $hab->name ?? null,
            'habitat_location' => $hab->location ?? null,
            'uploader_id'      => Mongo::oid($_SESSION['user_id']),
            'approval_status'  => 'pending',
            'created_at'       => new MongoDB\BSON\UTCDateTime(),
        ]);

        header('Location: my_submissions.php?submitted=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $title='Submit Species · Wildlife Explorer'; $css=['public','admin']; include __DIR__ . '/partials/head.php'; ?>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <span class="logo">&#127757;</span>
    Wildlife Explorer
  </div>
  <nav>
    <a class="btn ghost" href="index.php">Browse</a>
    <a class="btn ghost" href="my_submissions.php">My submissions</a>
    <a class="btn ghost" href="logout.php">Logout</a>
  </nav>
</header>

<main style="max-width:720px;margin:2rem auto;padding:0 1.5rem 4rem">
  <h1 style="margin-bottom:.25rem">Submit a species</h1>
  <p style="color:var(--slate-500);margin-top:0">Your submission will be reviewed by an admin before appearing on the public catalog.</p>

  <div class="form-card" style="margin-top:1.5rem">
    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-row">
        <label for="species_name">Species name</label>
        <input type="text" id="species_name" name="species_name" required
               value="<?= htmlspecialchars($_POST['species_name'] ?? '') ?>">
      </div>
      <div class="form-row">
        <label for="scientific_name">Scientific name</label>
        <input type="text" id="scientific_name" name="scientific_name"
               value="<?= htmlspecialchars($_POST['scientific_name'] ?? '') ?>">
      </div>
      <div class="form-row">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
          <option value="">— Choose —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (string)$c->_id ?>"><?= htmlspecialchars($c->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <label for="habitat_id">Habitat</label>
        <select id="habitat_id" name="habitat_id" required>
          <option value="">— Choose —</option>
          <?php foreach ($habitats as $h): ?>
            <option value="<?= (string)$h->_id ?>"><?= htmlspecialchars($h->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <label class="check"><input type="checkbox" name="is_endangered"> This species is endangered</label>
      </div>
      <div class="form-row">
        <label for="image_url">Image URL <span class="hint">optional, https://…</span></label>
        <input type="url" id="image_url" name="image_url" placeholder="https://…">
      </div>
      <div class="form-actions">
        <a href="index.php" class="btn ghost">Cancel</a>
        <button type="submit" class="btn">Submit for review</button>
      </div>
    </form>
  </div>
</main>

</body>
</html>
