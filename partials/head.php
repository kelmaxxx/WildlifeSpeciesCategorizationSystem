<?php
/**
 * Shared <head> block.
 *  $title  — page title
 *  $css    — array of extra CSS module names (e.g. ['public', 'detail']).
 *            base.css is always included.
 * Caller is expected to compute relative path to project root in $base
 * (defaults to '' meaning same folder as index.php).
 */
$title = $title ?? 'Wildlife Explorer';
$css   = $css   ?? [];
$base  = $base  ?? '';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>assets/css/base.css">
<?php foreach ($css as $module): ?>
<link rel="stylesheet" href="<?= $base ?>assets/css/<?= htmlspecialchars($module) ?>.css">
<?php endforeach; ?>
