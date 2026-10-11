<?php
// Installation : crée le premier compte administrateur. À SUPPRIMER après usage.
require_once __DIR__ . '/includes/auth.php';

$pdo = db();
$hasAdmin = (bool) $pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$errors = [];

if (!$hasAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom   = trim($_POST['nom'] ?? '');
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    if ($nom === '') $errors[] = 'Indiquez un nom.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail invalide.';
    if (strlen($pass) < 12) $errors[] = 'Utilisez au moins 12 caractères pour le mot de passe administrateur.';
    if (!$errors) {
        $pdo->prepare('INSERT INTO users (email, password_hash, role, nom) VALUES (?, ?, \'admin\', ?)')
            ->execute([$email, password_hash($pass, PASSWORD_DEFAULT), $nom]);
        login_user((int) $pdo->lastInsertId());
        flash('Compte administrateur créé. Pensez à supprimer le fichier install.php.');
        redirect('admin/index.php');
    }
}

$pageTitle = 'Installation';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <h1>Installation</h1>
    <?php if ($hasAdmin): ?>
        <p class="notice">Un administrateur existe déjà. Supprimez ce fichier <code>install.php</code> du serveur.</p>
    <?php else: ?>
        <p class="muted">Créez le premier compte administrateur.</p>
        <?php foreach ($errors as $err): ?><p class="flash flash-err" role="alert"><?= e($err) ?></p><?php endforeach; ?>
        <form class="form card" method="post">
            <?= csrf_field() ?>
            <label>Nom <input type="text" name="nom" required></label>
            <label>Adresse e-mail <input type="email" name="email" required></label>
            <label>Mot de passe <small>12 caractères minimum</small> <input type="password" name="password" autocomplete="new-password" required></label>
            <button class="btn" type="submit">Créer l'administrateur</button>
        </form>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
