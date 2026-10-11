<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_role(['admin', 'booker']);
$pdo = db();
$errors = [];
$v = ['titre' => '', 'type' => '', 'date_event' => '', 'lieu' => '', 'ville' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($v as $k => $_) { $v[$k] = trim($_POST[$k] ?? ''); }
    $d = DateTime::createFromFormat('Y-m-d', $v['date_event']);

    if ($v['titre'] === '' || mb_strlen($v['titre']) > 120) $errors[] = 'Le titre est obligatoire (120 caractères maximum).';
    if (!in_array($v['type'], TYPES_EVENEMENT, true)) $errors[] = 'Choisissez un type d\'évènement.';
    if (!$d || $d->format('Y-m-d') !== $v['date_event']) $errors[] = 'La date est invalide.';

    if (!$errors) {
        $pdo->prepare('INSERT INTO evenements (titre, type, date_event, lieu, ville, description, created_by) VALUES (?,?,?,?,?,?,?)')
            ->execute([$v['titre'], $v['type'], $v['date_event'], $v['lieu'], $v['ville'], $v['description'], $user['id']]);
        flash('Évènement créé. Vous pouvez maintenant y placer des artistes.');
        redirect('admin/evenement.php?id=' . $pdo->lastInsertId());
    }
}

$events = $pdo->query(
    "SELECT e.*, (SELECT COUNT(*) FROM affectations a WHERE a.evenement_id = e.id AND a.statut = 'accepte') AS confirmes,
                 (SELECT COUNT(*) FROM affectations a WHERE a.evenement_id = e.id) AS total
     FROM evenements e ORDER BY e.date_event DESC"
)->fetchAll();

$pageTitle = 'Évènements (admin)';
require __DIR__ . '/../includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <h1 style="font-size:clamp(2rem,4vw,3rem)">Évènements</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <div class="two-col">
        <div>
            <h2 style="font-size:1.7rem">Tous les évènements</h2>
            <?php if ($events): ?>
            <div class="table-scroll"><table>
                <thead><tr><th>Date</th><th>Évènement</th><th>Artistes</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><?= e(date_fr($ev['date_event'])) ?></td>
                        <td><strong><?= e($ev['titre']) ?></strong><br><span class="muted"><?= e($ev['type']) ?><?= $ev['ville'] ? ', ' . e($ev['ville']) : '' ?></span></td>
                        <td><?= (int) $ev['confirmes'] ?> confirmé(s) sur <?= (int) $ev['total'] ?></td>
                        <td><span class="badge <?= e($ev['statut']) ?>"><?= e(statut_label($ev['statut'])) ?></span></td>
                        <td><a class="btn btn-small btn-line" href="<?= e(url('admin/evenement.php?id=' . $ev['id'])) ?>">Gérer</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?>
                <p class="muted">Aucun évènement. Créez le premier avec le formulaire.</p>
            <?php endif; ?>
        </div>

        <div>
            <h2 style="font-size:1.7rem">Nouvel évènement</h2>
            <?php foreach ($errors as $err): ?><p class="flash flash-err" role="alert"><?= e($err) ?></p><?php endforeach; ?>
            <form class="form card" method="post">
                <?= csrf_field() ?>
                <label>Titre <input type="text" name="titre" value="<?= e($v['titre']) ?>" required></label>
                <div class="form-row">
                    <label>Type
                        <select name="type" required>
                            <option value="">Choisir…</option>
                            <?php foreach (TYPES_EVENEMENT as $t): ?><option <?= $v['type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                    <label>Date <input type="date" name="date_event" value="<?= e($v['date_event']) ?>" required></label>
                </div>
                <div class="form-row">
                    <label>Lieu <input type="text" name="lieu" value="<?= e($v['lieu']) ?>"></label>
                    <label>Ville <input type="text" name="ville" value="<?= e($v['ville']) ?>"></label>
                </div>
                <label>Description <textarea name="description"><?= e($v['description']) ?></textarea></label>
                <button class="btn" type="submit">Créer l'évènement</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
