</main>

<footer class="site-footer">
    <div class="wrap footer-inner">
        <div>
            <?php if ($logoFile): ?><img class="footer-logo" src="<?= e(url($logoFile)) ?>" alt="" width="150" height="105"><?php endif; ?>
            <p class="footer-brand"><?= e(SITE_NAME) ?></p>
            <p class="muted-light"><?= e(SITE_TAGLINE) ?></p>
        </div>
        <nav aria-label="Pied de page">
            <a href="<?= e(url('artistes.php')) ?>">Nos artistes</a>
            <a href="<?= e(url('evenements.php')) ?>">Évènements</a>
            <a href="<?= e(url('inscription.php')) ?>">Rejoindre l'agence</a>
            <a href="<?= e(url('connexion.php')) ?>">Espace artiste</a>
        </nav>
        <div>
            <p class="muted-light">Contact</p>
            <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>
        </div>
    </div>
    <p class="wrap footer-legal">© <?= date('Y') ?> <?= e(SITE_NAME) ?>. Mentions légales et conditions à compléter.</p>
</footer>

<script src="<?= e(url('assets/js/main.js')) ?>" defer></script>
</body>
</html>
