<?php
require_once __DIR__ . '/includes/auth.php';

$events = db()->query(
    "SELECT id, titre, type, date_event, lieu, ville, statut FROM evenements
     WHERE statut != 'annule' AND date_event >= date('now')
     ORDER BY date_event ASC"
)->fetchAll();

$mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
$pageTitle = 'Évènements';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap">
    <div class="page-head">
        <h1>Évènements à venir</h1>
        <p>Les soirées sur lesquelles l'agence place ses artistes.</p>
    </div>

    <?php if ($events): ?>
        <ul class="event-list" style="margin-bottom:5rem">
            <?php foreach ($events as $ev): $t = strtotime($ev['date_event']); ?>
                <li class="event-item">
                    <div class="date-chip"><strong><?= date('j', $t) ?></strong><span><?= e($mois[(int) date('n', $t) - 1]) ?></span></div>
                    <div>
                        <h3><?= e($ev['titre']) ?></h3>
                        <p><?= e($ev['type']) ?><?= $ev['ville'] ? ', ' . e($ev['ville']) : '' ?></p>
                    </div>
                    <span class="badge <?= e($ev['statut']) ?>"><?= e(statut_label($ev['statut'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="notice">Aucun évènement publié pour le moment. Créez votre profil artiste pour être prévenu en priorité.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
