<?php
require_once __DIR__ . '/auth.php';
$u = current_user();
$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME . ' | ' . SITE_TAGLINE;
$bodyClass = $bodyClass ?? '';

// Logo : dépose assets/img/logo.svg (ou .png / .webp) et il apparaîtra automatiquement
$logoFile = null;
foreach (['svg', 'png', 'webp'] as $ext) {
    if (is_file(__DIR__ . '/../assets/img/logo.' . $ext)) { $logoFile = 'assets/img/logo.' . $ext; break; }
}
$flash = flash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e(SITE_NAME) ?> met en relation des artistes et des événements : soirées, mariages, clubs, entreprises.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Figtree:wght@400;500;600;700&display=swap">
    <link rel="icon" type="image/png" href="<?= e(url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip" href="#contenu">Aller au contenu</a>

<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="<?= e(SITE_NAME) ?>, accueil">
            <span class="brand-mark<?= $logoFile ? ' has-logo' : '' ?>">
                <?php if ($logoFile): ?>
                    <img src="<?= e(url($logoFile)) ?>" alt="" height="56">
                <?php else: ?>
                    <?= e(mb_strtoupper(mb_substr(SITE_NAME, 0, 1))) ?>
                <?php endif; ?>
            </span>
            <span class="brand-name<?= $logoFile ? ' sr-only' : '' ?>"><?= e(SITE_NAME) ?></span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu">Menu</button>

        <nav id="menu" class="nav" aria-label="Navigation principale">
            <a href="<?= e(url('artistes.php')) ?>">Nos artistes</a>
            <a href="<?= e(url('evenements.php')) ?>">Évènements</a>
            <?php if ($u): ?>
                <a href="<?= e(url(home_for($u['role']))) ?>">Mon espace</a>
                <a class="btn btn-small btn-line" href="<?= e(url('deconnexion.php')) ?>">Déconnexion</a>
            <?php else: ?>
                <a href="<?= e(url('connexion.php')) ?>">Connexion</a>
                <a class="btn btn-small" href="<?= e(url('inscription.php')) ?>">Rejoindre l'agence</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="contenu">
<?php if ($flash): ?>
    <div class="wrap"><p class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></p></div>
<?php endif; ?>
