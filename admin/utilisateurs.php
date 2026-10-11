<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_role(['admin']);   // réservé aux administrateurs
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $uid = (int) ($_POST['id'] ?? 0);
    $role = $_POST['role'] ?? '';
    if ($uid === (int) $user['id']) {
        flash('Vous ne pouvez pas modifier votre propre rôle.', 'err');
    } elseif (in_array($role, ['artiste', 'booker', 'admin'], true)) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
        flash('Rôle mis à jour.');
    }
    redirect('admin/utilisateurs.php');
}

$rows = $pdo->query('SELECT id, nom, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$roles = ['artiste' => 'Artiste', 'booker' => 'Booker', 'admin' => 'Administrateur'];

$pageTitle = 'Comptes (admin)';
require __DIR__ . '/../includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <h1 style="font-size:clamp(2rem,4vw,3rem)">Comptes</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <p class="notice"><strong>Artiste</strong> : gère son profil et répond aux propositions. <strong>Booker</strong> : valide les artistes et place les artistes sur les évènements. <strong>Administrateur</strong> : tout cela, plus la gestion des comptes.</p>

    <div class="table-scroll"><table>
        <thead><tr><th>Nom</th><th>E-mail</th><th>Inscrit le</th><th>Rôle</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['nom']) ?></td>
                <td><?= e($r['email']) ?></td>
                <td><?= e(date_fr($r['created_at'])) ?></td>
                <td>
                    <?php if ((int) $r['id'] === (int) $user['id']): ?>
                        <?= e($roles[$r['role']]) ?> (vous)
                    <?php else: ?>
                        <form method="post" class="actions">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <select name="role" aria-label="Rôle de <?= e($r['nom']) ?>" style="width:auto">
                                <?php foreach ($roles as $k => $label): ?><option value="<?= $k ?>" <?= $r['role'] === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                            </select>
                            <button class="btn btn-small btn-line">Appliquer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
