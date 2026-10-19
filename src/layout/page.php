<?php

/**
 * @var string $title
 * @var string $description
 * @var string $keywords
 * @var string $author
 * @var string $favicon
 * @var string $css
 * @var string $js
 * @var string $lang
 * @var string $header
 * @var string $content
 * @var string $footer
 * */
?>
<!DOCTYPE html>
<html lang="<?= $lang ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Default Title' ?></title>
    <meta name="description" content="<?= $description ?? '' ?>">
    <meta name="keywords" content="<?= $keywords ?? '' ?>">
    <meta name="author" content="<?= $author ?? '' ?>">
    <?php if (!empty($favicon)): ?>
        <link rel="icon" href="<?= $favicon ?>" type="image/x-icon">
    <?php endif; ?>
<link rel="stylesheet" href="<?= $css ?? '/css/app.css' ?>">
</head>
<body>
<header>
    <?= $header ?? '' ?>
</header>
<main>
    <?= $content ?? '' ?>
</main>
<footer>
    <?= $footer ?? '' ?>
</footer>
<?php if (!empty($js)): ?>
    <script src="<?= $js ?? '/js/script.js' ?>"></script>
<?php endif; ?>
</body>
</html>
