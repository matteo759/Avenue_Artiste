<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_role(['artiste']);
$art = artiste_of((int) $user['id']);
if (!$art) { flash('Profil artiste introuvable.', 'err'); redirect('index.php'); }

// Réponse à une proposition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['affectation_id'] ?? 0);
    $rep = $_POST['reponse'] ?? '';
    if (in_array($rep, ['accepte', 'decline'], true) && $art['statut'] === 'valide') {
        $st = db()->prepare("UPDATE affectations SET statut = ? WHERE id = ? AND artiste_id = ?");
        $st->execute([$rep, $id, $art['id']]);
        flash($rep === 'accepte' ? 'Proposition acceptée. L\'équipe vous recontacte pour les détails.' : 'Proposition déclinée.');
    }
    redirect('artiste/index.php');
}

$st = db()->prepare(
    "SELECT a.id, a.statut, a.cachet, a.note, e.titre, e.type, e.date_event, e.lieu, e.ville, e.statut AS ev_statut
     FROM affectations a JOIN evenements e ON e.id = a.evenement_id
     WHERE a.artiste_id = ? ORDER BY e.date_event ASC"
);
$st->execute([$art['id']]);
$props = $st->fetchAll();

$pageTitle = 'Mon espace';
require __DIR__ . '/../includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <h1 style="font-size:clamp(2rem,4vw,3rem)">Bonjour <?= e($art['nom_scene']) ?></h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <?php if ($art['statut'] === 'en_attente'): ?>
        <p class="notice">Votre profil est en cours de relecture. Vous recevrez des propositions une fois qu'il sera validé. En attendant, <a href="<?= e(url('artiste/profil.php')) ?>">complétez-le</a> : une photo et une bio augmentent vos chances.</p>
    <?php elseif ($art['statut'] === 'refuse'): ?>
        <p class="notice">Votre profil n'a pas été retenu pour le moment. Écrivez-nous à <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a> pour en discuter.</p>
    <?php endif; ?>

    <h2 style="font-size:1.7rem">Mes propositions</h2>
    <?php if ($props): ?>
        <div class="table-scroll">
        <table>
            <thead><tr><th>Date</th><th>Évènement</th><th>Lieu</th><th>Cachet</th><th>Statut</th><th>Réponse</th></tr></thead>
            <tbody>
            <?php foreach ($props as $p): ?>
                <tr>
                    <td><?= e(date_fr($p['date_event'])) ?></td>
                    <td><strong><?= e($p['titre']) ?></strong><br><span class="muted"><?= e($p['type']) ?></span><?php if ($p['note']): ?><br><small><?= e($p['note']) ?></small><?php endif; ?></td>
                    <td><?= e(trim($p['lieu'] . ' ' . $p['ville'])) ?></td>
                    <td><?= $p['cachet'] !== null ? e(number_format((int) $p['cachet'], 0, ',', ' ')) . ' €' : 'À définir' ?></td>
                    <td><span class="badge <?= e($p['statut']) ?>"><?= e(statut_label($p['statut'])) ?></span></td>
                    <td>
                        <?php if ($p['statut'] === 'propose' && $art['statut'] === 'valide'): ?>
                            <form method="post" class="actions">
                                <?= csrf_field() ?>
                                <input type="hidden" name="affectation_id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-small" name="reponse" value="accepte">Accepter</button>
                                <button class="btn btn-small btn-danger" name="reponse" value="decline" data-confirm="Décliner cette proposition ?">Décliner</button>
                            </form>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p class="muted">Aucune proposition pour l'instant. Elles apparaîtront ici dès que l'équipe vous placera sur un évènement.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
