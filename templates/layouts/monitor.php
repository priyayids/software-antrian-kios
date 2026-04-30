<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Monitor Antrian') ?></title>
    <link href="<?= asset('assets/img/favicon.ico') ?>" type="image/x-icon" rel="shortcut icon">
    <link href="<?= asset('assets/vendor/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/vendor/css/bootstrap-icons.css') ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>

<body>
    <?= $content ?? '' ?>

    <script src="<?= asset('assets/vendor/js/jquery-3.6.0.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('assets/vendor/js/popper.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('assets/vendor/js/bootstrap.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('assets/vendor/js/responsivevoice.js') ?>" type="text/javascript"></script>

    <?php if (isset($inlineScript)): ?>
        <script><?= $inlineScript ?></script>
    <?php endif; ?>
</body>

</html>
