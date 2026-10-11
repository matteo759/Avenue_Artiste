<?php $cur = basename($_SERVER['SCRIPT_NAME']); ?>
<nav class="dash-nav" aria-label="Espace artiste">
    <a href="<?= e(url('artiste/index.php')) ?>" class="<?= $cur === 'index.php' ? 'is-active' : '' ?>">Mes propositions</a>
    <a href="<?= e(url('artiste/profil.php')) ?>" class="<?= $cur === 'profil.php' ? 'is-active' : '' ?>">Mon profil</a>
</nav>
