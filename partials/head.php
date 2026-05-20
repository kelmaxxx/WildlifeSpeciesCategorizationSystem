<?php
/* partials/head.php — head snippet
 * Loads fonts + base styles and applies the saved light/forest theme
 * before paint to avoid a flash.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title ?? 'Wildlife Species Categorization System') ?></title>

  <!-- Type stack: Inter (sans), Fraunces (display serif), IBM Plex Mono (mono) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/public.css">
  <?php if (!empty($page_css)): foreach ($page_css as $css): ?>
    <link rel="stylesheet" href="assets/css/<?= htmlspecialchars($css) ?>">
  <?php endforeach; endif; ?>

  <script>
    (function () {
      var saved = null;
      try { saved = localStorage.getItem('wscs-theme'); } catch (e) {}
      var theme = (saved === 'forest' || saved === 'light') ? saved : 'light';
      document.documentElement.setAttribute('data-theme-pref', theme);
      document.addEventListener('DOMContentLoaded', function () {
        document.body.setAttribute('data-theme', theme);
      });
    })();
  </script>
</head>
<body data-theme="light">
