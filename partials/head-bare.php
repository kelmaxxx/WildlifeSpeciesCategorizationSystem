<?php
/* partials/head-bare.php — head fragment for auth screens
 *
 * Like head.php but does NOT close <body> after, does NOT include the
 * topbar. Auth pages use a full-bleed split layout and provide their
 * own brand mark inside the left plate.
 *
 * Pages that include this MUST close </body></html> themselves.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title ?? 'Wildlife Catalog') ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/base.css">
  <?php if (!empty($page_css)): foreach ($page_css as $css): ?>
    <link rel="stylesheet" href="assets/css/<?= htmlspecialchars($css) ?>">
  <?php endforeach; endif; ?>
</head>
<body data-theme="cream">
