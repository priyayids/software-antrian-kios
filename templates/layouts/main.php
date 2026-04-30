<!doctype html>
<html lang="id" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aplikasi Antrian NISCAYA">
    <meta name="author" content="NISCAYA">

    <title><?= htmlspecialchars($title ?? 'ANTRIAN NISCAYA') ?></title>

    <link href="<?= asset('assets/img/favicon.ico') ?>" type="image/x-icon" rel="shortcut icon">
    <link href="<?= asset('assets/vendor/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/vendor/css/bootstrap-icons.css') ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">

    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link href="<?= asset($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="d-flex flex-column h-100">
    <main class="flex-shrink-0">
        <?= $content ?? '' ?>
    </main>

    <footer class="footer mt-auto py-4">
        <div class="container">
            <div class="copyright text-center">
                &copy; <?= date('Y') ?> - <a href="https://niscaya.id/" target="_blank" class="text-brand text-decoration-none">niscaya.id</a>. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="<?= asset('assets/vendor/js/jquery-3.6.0.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('assets/vendor/js/popper.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('assets/vendor/js/bootstrap.min.js') ?>" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" type="text/javascript"></script>

    <?php if (isset($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= asset($js) ?>" type="text/javascript"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($inlineScript)): ?>
        <script><?= $inlineScript ?></script>
    <?php endif; ?>
</body>

</html>
