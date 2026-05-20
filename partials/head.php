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
<link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>assets/css/base.css">
<?php foreach ($css as $module): ?>
<link rel="stylesheet" href="<?= $base ?>assets/css/<?= htmlspecialchars($module) ?>.css">
<?php endforeach; ?>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    corePlugins: { preflight: false },
    theme: {
      extend: {
        colors: {
          brand: {
            50:  '#f3faf5',
            100: '#e6f4ea',
            500: '#3cb371',
            600: '#2c8a4a',
            700: '#1f7a3b',
            900: '#14532d',
          },
        },
        fontFamily: {
          sans: ['"Google Sans"', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
        },
      },
    },
  };
</script>
