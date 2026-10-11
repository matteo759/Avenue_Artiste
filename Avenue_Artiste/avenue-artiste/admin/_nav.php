<?php $cur = basename($_SERVER['SCRIPT_NAME']); $isAdmin = ($user['role'] ?? '') === 'admin'; ?>
<nav class="dash-nav" aria-label="Administration">
    <a href="<?= e(url('admin/index.php')) ?>" class="<?= $cur === 'index.php' ? 'is-active' : '' ?>">Tableau de bord</a>
    <a href="<?= e(url('admin/artistes.php')) ?>" class="<?= $cur === 'artistes.php' ? 'is-active' : '' ?>">Artistes</a>
    <a href="<?= e(url('admin/evenements.php')) ?>" class="<?= in_array($cur, ['evenements.php', 'evenement.php'], true) ? 'is-active' : '' ?>">Évènements</a>
    <?php if ($isAdmin): ?>
        <a href="<?= e(url('admin/utilisateurs.php')) ?>" class="<?= $cur === 'utilisateurs.php' ? 'is-active' : '' ?>">Comptes</a>
    <?php endif; ?>
</nav>
