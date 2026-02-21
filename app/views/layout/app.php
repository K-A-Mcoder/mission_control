<!DOCTYPE html>
<html lang="en">

<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($title ?? 'Etus Framework') ?>
    </title>
    <?= partial('partials/style') ?>
</head>

<body class="selection:text-white selection:bg-primary">

    <div id="main-wrapper">
        <!-- Nav header @s -->
        <?= partial('partials/nav-header') ?>

        <!-- Header @s -->
        <?= partial('partials/header') ?>

        <!-- Sidebar @s -->
        <?= partial('partials/sidebar') ?>

        <!-- Content body start -->
        <div class="content-body">
            <?= $content ?>
        </div>
        <!-- @S Footer -->
        <?= partial('partials/footer') ?>

    </div>

    <?= partial('partials/scripts'); ?>

</body>

</html>