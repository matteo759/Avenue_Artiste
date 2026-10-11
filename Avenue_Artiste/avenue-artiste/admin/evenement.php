<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_role(['admin', 'booker']);
$pdo = db();

$id = (int) ($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM evenements WHERE id = ?');
$st->execute([$id]);
$ev = $st->fetch();
if (!$ev) { http_response_code(404); exit('Évènement introuvable.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'placer') {
        $artisteId = (int) ($_POST['artiste_id'] ?? 0);
        $cachet = ($_POST['cachet'] ?? '') === '' ? null : max(0, (int) $_POST['cachet']);
        $note = trim($_POST['note'] ?? '');
        $ok = $pdo->prepare("SELECT 1 FROM artistes WHERE id = ? AND statut = 'valide'");
        $ok->execute([$artisteId]);
        if (!$ok->fetch()) {
            flash('Seuls les artistes validés peuvent être placés sur un évènement.', 'err');
        } else {
            $ins = $pdo->prepare('INSERT OR IGNORE INTO affectations (evenement_id, artiste_id, cachet, note) VALUES (?,?,?,?)');
            $ins->execute([$id, $artisteId, $cachet, $note]);
            flash($ins->rowCount() ? 'Artiste placé. Il verra la proposition dans son espace.' : 'Cet artiste est déjà placé sur cet évènement.', $ins->rowCount() ? 'ok' : 'err');
        }
    } elseif ($action === 'retirer') {
        $pdo->prepare('DELETE FROM affectations WHERE id = ? AND evenement_id = ?')->execute([(int) ($_POST['affectation_id'] ?? 0), $id]);
        flash('Artiste retiré de l\'évènement.');
    } elseif ($action === 'statut') {
        $s = $_POST['statut'] ?? '';
        if (in_array($s, ['ouvert', 'complet', 'annule'], true)) {
            $pdo->prepare('UPDATE evenements SET statut = ? WHERE id = ?')->execute([$s, $id]);
            flash('Statut de l\'évènement mis à jour.');
        }
    } elseif ($action === 'supprimer' && $user['role'] === 'admin') {
        $pdo->prepare('DELETE FROM evenements WHERE id = ?')->execute([$id]);
        flash('Évènement supprimé.');
        redirect('admin/evenements.php');
    }
    redirect('admin/evenement.php?id=' . $id);
}

$st = $pdo->prepare(
    'SELECT af.id, af.statut, af.cachet, af.note, a.nom_scene, a.discipline, u.email
     FROM affectations af JOIN artistes a ON a.id = af.artiste_id JOIN users u ON u.id = a.user_id
     WHERE af.evenement_id = ? ORDER BY af.created_at'
);
$st->execute([$id]);
$places = $st->fetchAll();

// Artistes validés pas encore placés ici
$st = $pdo->prepare(
    "SELECT id, nom_scene, discipline, ville FROM artistes
     WHERE statut = 'valide' AND id NOT IN (SELECT artiste_id FROM affectations WHERE evenement_id = ?)
     ORDER BY nom_scene COLLATE NOCASE"
);
$st->execute([$id]);
$disponibles = $st->fetchAll();

$pageTitle = $ev['titre'];
require __DIR__ . '/../includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <h1 style="font-size:clamp(2rem,4vw,3rem)"><?= e($ev['titre']) ?></h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <p class="notice">
        <strong><?= e(date_fr($ev['date_event'])) ?></strong>, <?= e($ev['type']) ?>
        <?= $ev['lieu'] || $ev['ville'] ? ' à ' . e(trim($ev['lieu'] . ' ' . $ev['ville'])) : '' ?>
        <?php if ($ev['description']): ?><br><?= nl2br(e($ev['description'])) ?><?php endif; ?>
    </p>

    <div class="two-col">
        <div>
            <h2 style="font-size:1.7rem">Artistes placés</h2>
            <?php if ($places): ?>
            <div class="table-scroll"><table>
                <thead><tr><th>Artiste</th><th>Cachet</th><th>Réponse</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($places as $p): ?>
                    <tr>
                        <td><strong><?= e($p['nom_scene']) ?></strong><br><span class="muted"><?= e($p['discipline']) ?></span></td>
                        <td><?= $p['cachet'] !== null ? e(number_format((int) $p['cachet'], 0, ',', ' ')) . ' €' : 'À définir' ?></td>
                        <td><span class="badge <?= e($p['statut']) ?>"><?= e(statut_label($p['statut'])) ?></span></td>
                        <td>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="retirer">
                                <input type="hidden" name="affectation_id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-small btn-danger" data-confirm="Retirer cet artiste de l'évènement ?">Retirer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?>
                <p class="muted">Personne n'est encore placé sur cet évènement.</p>
            <?php endif; ?>

            <h2 style="font-size:1.4rem;margin-top:2.5rem">Statut de l'évènement</h2>
            <form method="post" class="actions">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="statut">
                <?php foreach (['ouvert', 'complet', 'annule'] as $s): ?>
                    <button class="btn btn-small <?= $ev['statut'] === $s ? '' : 'btn-line' ?>" name="statut" value="<?= $s ?>"><?= e(statut_label($s)) ?></button>
                <?php endforeach; ?>
            </form>
            <?php if ($user['role'] === 'admin'): ?>
                <form method="post" style="margin-top:1.5rem">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="supprimer">
                    <button class="btn btn-small btn-danger" data-confirm="Supprimer définitivement cet évènement et ses propositions ?">Supprimer l'évènement</button>
                </form>
            <?php endif; ?>
        </div>

        <div>
            <h2 style="font-size:1.7rem">Placer un artiste</h2>
            <?php if ($disponibles): ?>
            <form class="form card" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="placer">
                <label>Artiste
                    <select name="artiste_id" required>
                        <?php foreach ($disponibles as $a): ?>
                            <option value="<?= (int) $a['id'] ?>"><?= e($a['nom_scene']) ?> (<?= e($a['discipline']) ?><?= $a['ville'] ? ', ' . e($a['ville']) : '' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Cachet proposé en € <small>Laissez vide si à définir.</small> <input type="number" name="cachet" min="0" step="10"></label>
                <label>Message pour l'artiste <small>Horaires, durée, tenue, matériel…</small> <textarea name="note" style="min-height:90px"></textarea></label>
                <button class="btn" type="submit">Envoyer la proposition</button>
            </form>
            <?php else: ?>
                <p class="muted">Aucun artiste validé disponible. <a href="<?= e(url('admin/artistes.php?statut=en_attente')) ?>">Validez des profils</a> pour les placer.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
