<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_role(['admin', 'booker']);
$pdo = db();

$n = fn(string $sql) => (int) $pdo->query($sql)->fetchColumn();
$stats = [
    'Artistes à valider'   => $n("SELECT COUNT(*) FROM artistes WHERE statut = 'en_attente'"),
    'Artistes validés'     => $n("SELECT COUNT(*) FROM artistes WHERE statut = 'valide'"),
    'Évènements à venir'   => $n("SELECT COUNT(*) FROM evenements WHERE statut != 'annule' AND date_event >= date('now')"),
    'Propositions en attente de réponse' => $n("SELECT COUNT(*) FROM affectations WHERE statut = 'propose'"),
];
$attente = $pdo->query("SELECT a.id, a.nom_scene, a.discipline, a.ville, u.email FROM artistes a JOIN users u ON u.id = a.user_id WHERE a.statut = 'en_attente' ORDER BY a.created_at ASC LIMIT 8")->fetchAll();

$pageTitle = 'Administration';
require __DIR__ . '/../includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <h1 style="font-size:clamp(2rem,4vw,3rem)">Administration</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <div class="stats">
        <?php foreach ($stats as $label => $val): ?>
            <div class="stat"><strong><?= $val ?></strong><?= e($label) ?></div>
        <?php endforeach; ?>
    </div>

    <h2 style="font-size:1.7rem">Profils à valider</h2>
    <?php if ($attente): ?>
        <div class="table-scroll"><table>
            <thead><tr><th>Artiste</th><th>Discipline</th><th>Ville</th><th>E-mail</th></tr></thead>
            <tbody>
            <?php foreach ($attente as $a): ?>
                <tr><td><?= e($a['nom_scene']) ?></td><td><?= e($a['discipline']) ?></td><td><?= e($a['ville']) ?></td><td><?= e($a['email']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <p style="margin-top:1rem"><a class="btn btn-small" href="<?= e(url('admin/artistes.php?statut=en_attente')) ?>">Traiter les demandes</a></p>
    <?php else: ?>
        <p class="muted">Aucun profil en attente.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
