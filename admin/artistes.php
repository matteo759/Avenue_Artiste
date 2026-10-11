<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_role(['admin', 'booker']);
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $statut = $_POST['statut'] ?? '';
    if (in_array($statut, ['en_attente', 'valide', 'refuse'], true)) {
        $pdo->prepare('UPDATE artistes SET statut = ? WHERE id = ?')->execute([$statut, $id]);
        flash('Statut mis à jour : ' . mb_strtolower(statut_label($statut)) . '.');
    }
    redirect('admin/artistes.php' . (!empty($_GET['statut']) ? '?statut=' . urlencode($_GET['statut']) : ''));
}

$filtre = $_GET['statut'] ?? '';
$sql = 'SELECT a.id, a.nom_scene, a.discipline, a.ville, a.statut, u.nom, u.email FROM artistes a JOIN users u ON u.id = a.user_id';
$params = [];
if (in_array($filtre, ['en_attente', 'valide', 'refuse'], true)) { $sql .= ' WHERE a.statut = ?'; $params[] = $filtre; } else { $filtre = ''; }
$sql .= ' ORDER BY a.created_at DESC';
$st = $pdo->prepare($sql); $st->execute($params);
$rows = $st->fetchAll();

$pageTitle = 'Artistes (admin)';
require __DIR__ . '/../includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <h1 style="font-size:clamp(2rem,4vw,3rem)">Artistes</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <div class="chips" style="margin-bottom:1.5rem">
        <a class="chip <?= $filtre === '' ? 'is-active' : '' ?>" href="<?= e(url('admin/artistes.php')) ?>">Tous</a>
        <?php foreach (['en_attente', 'valide', 'refuse'] as $s): ?>
            <a class="chip <?= $filtre === $s ? 'is-active' : '' ?>" href="<?= e(url('admin/artistes.php?statut=' . $s)) ?>"><?= e(statut_label($s)) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($rows): ?>
    <div class="table-scroll"><table>
        <thead><tr><th>Nom d'artiste</th><th>Contact</th><th>Discipline</th><th>Ville</th><th>Statut</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $a): ?>
            <tr>
                <td><strong><?= e($a['nom_scene']) ?></strong></td>
                <td><?= e($a['nom']) ?><br><span class="muted"><?= e($a['email']) ?></span></td>
                <td><?= e($a['discipline']) ?></td>
                <td><?= e($a['ville']) ?></td>
                <td><span class="badge <?= e($a['statut']) ?>"><?= e(statut_label($a['statut'])) ?></span></td>
                <td>
                    <form method="post" class="actions">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                        <?php if ($a['statut'] !== 'valide'): ?><button class="btn btn-small" name="statut" value="valide">Valider</button><?php endif; ?>
                        <?php if ($a['statut'] !== 'refuse'): ?><button class="btn btn-small btn-danger" name="statut" value="refuse" data-confirm="Refuser ce profil ?">Refuser</button><?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php else: ?>
        <p class="muted">Aucun artiste dans cette catégorie.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
