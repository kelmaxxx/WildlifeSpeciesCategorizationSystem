<?php
require_once __DIR__ . '/public_auth.php';
require_user();

$categories = $db->find('categories', [], ['sort' => ['name' => 1]]);
$habitats   = $db->find('habitats',   [], ['sort' => ['name' => 1]]);

$error = null;
$old   = [
    'species_name'    => $_POST['species_name']    ?? '',
    'scientific_name' => $_POST['scientific_name'] ?? '',
    'category_id'     => $_POST['category_id']     ?? '',
    'habitat_id'      => $_POST['habitat_id']      ?? '',
    'image_url'       => $_POST['image_url']       ?? '',
    'is_endangered'   => (($_POST['is_endangered'] ?? '') === '1'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name       = trim($_POST['species_name']    ?? '');
    $sci        = trim($_POST['scientific_name'] ?? '');
    $catId      = Mongo::oid($_POST['category_id'] ?? null);
    $habId      = Mongo::oid($_POST['habitat_id']  ?? null);
    $endangered = ($_POST['is_endangered'] ?? '') === '1';
    $imgUrl     = trim($_POST['image_url'] ?? '');

    if ($name === '' || !$catId || !$habId) {
        $error = 'Name, diet, and habitat are required.';
    } elseif ($imgUrl !== '' && !preg_match('#^https?://#i', $imgUrl)) {
        $error = 'Image URL must start with http:// or https://.';
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

function checked_if($cond): string { return $cond ? ' checked' : ''; }
function selected_if(string $a, string $b): string { return $a === $b ? ' selected' : ''; }

$page_title = 'Contribute a specimen — Wildlife Catalog';
$page_css   = ['submit.css'];
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/topbar.php';
?>

<section class="frame">
  <div class="crumb">
    <a href="index.php">Catalog</a>
    <span class="sep">/</span>
    <span class="here">Contribute a specimen</span>
  </div>

  <div class="page-head">
    <div>
      <div class="eyebrow">For contributors · est. read 2 min</div>
      <h1 class="display">Contribute<br><i class="accent">a specimen.</i></h1>
    </div>
    <p class="intro">
      Add a species to the catalog. Submissions are reviewed by our editors before they appear in the public index.
    </p>
  </div>

  <?php if ($error): ?>
    <div class="alert error" style="margin-bottom:24px"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="submit-grid">
    <form class="contribute" id="contribute-form" method="post" action="submit_species.php" novalidate>
      <?= csrf_field() ?>

      <fieldset class="fset">
        <legend>
          <span class="num">§ 01</span>
          <h2>Identification</h2>
          <span class="req">Required</span>
        </legend>
        <p class="hint-row">What is this animal called — in the wild, and in the books?</p>

        <div class="frow">
          <label for="species_name">Common name</label>
          <input id="species_name" name="species_name" type="text" value="<?= htmlspecialchars($old['species_name']) ?>"
                 placeholder="e.g. Philippine Eagle" required>
        </div>

        <div class="frow">
          <label for="scientific_name">Scientific name <span class="opt">Genus species</span></label>
          <input id="scientific_name" name="scientific_name" type="text" value="<?= htmlspecialchars($old['scientific_name']) ?>"
                 placeholder="e.g. Pithecophaga jefferyi">
          <span class="helper">Italicised in the catalog. Capitalise genus, lowercase species.</span>
        </div>
      </fieldset>

      <fieldset class="fset">
        <legend>
          <span class="num">§ 02</span>
          <h2>Ecology</h2>
          <span class="req">Required</span>
        </legend>
        <p class="hint-row">How does it live, and how worried should we be about it?</p>

        <div class="frow">
          <label>Diet</label>
          <div class="seg" data-group="diet">
            <?php foreach ($categories as $c):
                $cid = (string) $c->_id;
                $slug = strtolower($c->name); ?>
              <label data-d="<?= htmlspecialchars($slug) ?>">
                <input type="radio" name="category_id" value="<?= htmlspecialchars($cid) ?>"<?= checked_if($old['category_id'] === $cid) ?>>
                <?= htmlspecialchars($c->name) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="frow">
          <label>Conservation status</label>
          <div class="seg" data-group="status">
            <label data-s="stable">
              <input type="radio" name="is_endangered" value="0"<?= checked_if(!$old['is_endangered']) ?>>
              Least concern
            </label>
            <label data-s="endangered">
              <input type="radio" name="is_endangered" value="1"<?= checked_if($old['is_endangered']) ?>>
              Endangered
            </label>
          </div>
          <span class="helper">Mark as endangered only when populations are in decline.</span>
        </div>

        <div class="frow">
          <label for="habitat_id">Habitat</label>
          <select id="habitat_id" name="habitat_id" required>
            <option value="" disabled<?= $old['habitat_id'] === '' ? ' selected' : '' ?>>Choose a habitat…</option>
            <?php foreach ($habitats as $h):
                $hid = (string) $h->_id; ?>
              <option value="<?= htmlspecialchars($hid) ?>"<?= selected_if($old['habitat_id'], $hid) ?>>
                <?= htmlspecialchars($h->name) ?><?= !empty($h->location) ? ' — ' . htmlspecialchars($h->location) : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </fieldset>

      <fieldset class="fset">
        <legend>
          <span class="num">§ 03</span>
          <h2>Photograph</h2>
          <span class="req">Optional</span>
        </legend>
        <p class="hint-row">Drop in a URL to a photo. Editors will request a higher-resolution version if needed.</p>

        <div class="photo-row">
          <div class="input-stack">
            <div class="frow">
              <label for="image_url">Image URL</label>
              <input id="image_url" name="image_url" type="url" value="<?= htmlspecialchars($old['image_url']) ?>" placeholder="https://…/eagle.jpg">
              <span class="helper">Public, licensed image. Must start with https://.</span>
            </div>
          </div>
          <div class="photo-thumb" id="thumb">
            <?php if (!empty($old['image_url'])): ?>
              <div class="img" style="background-image:url('<?= htmlspecialchars($old['image_url']) ?>')"></div>
            <?php else: ?>
              <div class="ph">Preview<br>appears here</div>
            <?php endif; ?>
          </div>
        </div>
      </fieldset>

      <div class="submit-row">
        <span style="font-family:var(--mono);font-size:12px;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.1em">
          Submissions enter the catalog with status: <b style="color:var(--status-vuln)">pending</b>
        </span>
        <a href="index.php" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
          Submit for review <span class="arrow" aria-hidden="true"></span>
        </button>
      </div>
    </form>

    <aside class="side">
      <div class="panel preview">
        <div class="head">
          <h3>Live preview</h3>
          <span class="tip">As it will appear in the catalog</span>
        </div>
        <div class="preview-card">
          <div class="pc-img" id="pc-img">
            <?php if (!empty($old['image_url'])): ?>
              <div class="img" style="background-image:url('<?= htmlspecialchars($old['image_url']) ?>')"></div>
            <?php endif; ?>
            <div class="ph"<?= !empty($old['image_url']) ? ' style="display:none"' : '' ?>>
              <div class="sil"></div>
              <div>Photograph forthcoming</div>
            </div>
            <span class="num-tag">№???</span>
            <span class="status-dot" id="pc-dot" data-s="<?= $old['is_endangered'] ? 'endangered' : 'stable' ?>"></span>
          </div>
          <div>
            <h3 class="common<?= empty($old['species_name']) ? ' empty' : '' ?>" id="pc-common">
              <?= !empty($old['species_name']) ? htmlspecialchars($old['species_name']) : 'Untitled specimen' ?>
            </h3>
            <div class="latin<?= empty($old['scientific_name']) ? ' empty' : '' ?>" id="pc-latin">
              <?= !empty($old['scientific_name']) ? htmlspecialchars($old['scientific_name']) : 'Scientific name pending' ?>
            </div>
          </div>
          <div class="foot">
            <span class="st" id="pc-status" data-s="<?= $old['is_endangered'] ? 'endangered' : 'stable' ?>">
              <?= $old['is_endangered'] ? 'Endangered' : 'Least concern' ?>
            </span>
            <span id="pc-habitat">Habitat pending</span>
          </div>
        </div>
      </div>

      <div class="panel guidelines">
        <h3>Editorial guidelines</h3>
        <ol>
          <li><b>Real animals only.</b> Verify in two independent references before submitting.</li>
          <li><b>Common name.</b> Use the most widely accepted English name.</li>
          <li><b>Photograph rights.</b> Public domain, Creative Commons, or your own work.</li>
          <li><b>Conservation status.</b> Mark as endangered only with strong evidence.</li>
          <li><b>Tone.</b> Field note, not encyclopaedia. Curiosity over exhaustion.</li>
        </ol>
      </div>

      <div class="panel">
        <h3>Review timeline</h3>
        <p style="margin:0;font-size:14px;color:var(--ink-soft)">
          Submissions are reviewed by our editors before they appear in the public index.
          Track every submission you've made on your
          <a href="my_submissions.php" style="color:var(--ink);text-decoration:underline;text-decoration-color:var(--rule);text-underline-offset:3px">submissions page</a>.
        </p>
      </div>
    </aside>
  </div>
</section>

<script src="assets/js/submit-preview.js"></script>

<?php include __DIR__ . '/partials/footer.php'; ?>
