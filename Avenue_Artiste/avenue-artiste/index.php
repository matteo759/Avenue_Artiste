<?php
require_once __DIR__ . '/includes/auth.php';

$bodyClass = 'home';
$pdo = db();

$prochaines = $pdo->query(
    "SELECT id, titre, type, date_event, ville FROM evenements
     WHERE statut = 'ouvert' AND date_event >= date('now')
     ORDER BY date_event ASC LIMIT 4"
)->fetchAll();

$vedettes = $pdo->query(
    "SELECT id, nom_scene, discipline, ville, photo FROM artistes
     WHERE statut = 'valide' ORDER BY id DESC LIMIT 4"
)->fetchAll();

$mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-beams" aria-hidden="true"></div>
    <div class="wrap hero-grid">
        <div>
            <h1>Votre talent mérite la meilleure scène.</h1>
            <p class="hero-lead">
                <?= e(SITE_NAME) ?> place des artistes dans des soirées, mariages, clubs et événements d'entreprise.
                Un seul profil, des dates sérieuses, un cachet annoncé avant que vous ne disiez oui.
            </p>
            <div class="hero-actions">
                <a class="btn" href="<?= e(url('inscription.php')) ?>">Créer mon profil artiste</a>
                <a class="btn btn-line" href="<?= e(url('artistes.php')) ?>">Découvrir les artistes</a>
            </div>
        </div>

        <aside class="dates" aria-labelledby="dates-titre">
            <h2 id="dates-titre">Prochaines dates</h2>
            <?php if ($prochaines): ?>
                <?php foreach ($prochaines as $ev): $t = strtotime($ev['date_event']); ?>
                    <div class="date-row">
                        <div class="date-chip"><strong><?= date('j', $t) ?></strong><span><?= e($mois[(int) date('n', $t) - 1]) ?></span></div>
                        <div>
                            <p class="t"><?= e($ev['titre']) ?></p>
                            <p class="muted"><?= e($ev['type']) ?><?= $ev['ville'] ? ', ' . e($ev['ville']) : '' ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a class="dates-more" href="<?= e(url('evenements.php')) ?>">Voir tous les évènements</a>
            <?php else: ?>
                <p class="dates-empty">Les premières dates arrivent. Créez votre profil pour être proposé dès leur ouverture.</p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<section class="trust" aria-label="Nos engagements">
    <div class="wrap trust-grid">
        <div class="trust-item">
            <h3>Cachet annoncé d'avance</h3>
            <p>Chaque proposition indique le montant avant votre réponse. Aucune mauvaise surprise.</p>
        </div>
        <div class="trust-item">
            <h3>Une équipe qui répond</h3>
            <p>Un interlocuteur humain, du premier échange jusqu'au soir de la prestation.</p>
        </div>
        <div class="trust-item">
            <h3>Profils relus un par un</h3>
            <p>L'équipe valide chaque artiste avant de le proposer. Votre image reste protégée.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <h2>Tous les sons qui font danser</h2>
            <p>Choisissez votre discipline pour voir qui nous accompagne déjà.</p>
        </div>
        <div class="chips">
            <?php foreach (DISCIPLINES as $d): ?>
                <a class="chip" href="<?= e(url('artistes.php?discipline=' . urlencode($d))) ?>"><?= e($d) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="wrap">
        <div class="section-head">
            <h2>Comment ça se passe</h2>
        </div>
        <ol class="steps">
            <li><h3>Créez votre compte</h3><p>Présentez-vous en quelques minutes : discipline, ville, bio, photo.</p></li>
            <li><h3>L'équipe valide</h3><p>Nous relisons votre profil et vous répondons rapidement.</p></li>
            <li><h3>On vous propose des dates</h3><p>Lieu, horaire et cachet détaillés dans votre espace personnel.</p></li>
            <li><h3>Vous acceptez, vous jouez</h3><p>Un clic pour confirmer. Nous gérons le reste avec l'organisateur.</p></li>
        </ol>
    </div>
</section>

<?php if ($vedettes): ?>
<section class="section">
    <div class="wrap">
        <div class="section-head"><h2>Ils nous ont rejoints</h2></div>
        <div class="artist-grid">
            <?php foreach ($vedettes as $a): ?>
                <a class="artist" href="<?= e(url('artiste.php?id=' . $a['id'])) ?>">
                    <div class="artist-photo">
                        <?php if ($a['photo']): ?><img src="<?= e(url('uploads/' . $a['photo'])) ?>" alt="" loading="lazy"><?php else: ?><?= e(mb_strtoupper(mb_substr($a['nom_scene'], 0, 1))) ?><?php endif; ?>
                    </div>
                    <h3><?= e($a['nom_scene']) ?></h3>
                    <p><?= e($a['discipline']) ?><?= $a['ville'] ? ', ' . e($a['ville']) : '' ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="wrap">
        <h2>Prêt à monter sur scène ?</h2>
        <p>Rejoignez l'agence et recevez vos premières propositions de dates.</p>
        <a class="btn" href="<?= e(url('inscription.php')) ?>">Rejoindre l'agence</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
