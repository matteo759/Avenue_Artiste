<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) redirect(home_for(current_user()['role']));

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    // Limite simple : 5 essais ratés, puis 5 minutes d'attente
    $_SESSION['tries'] = array_filter($_SESSION['tries'] ?? [], fn($t) => $t > time() - 300);
    if (count($_SESSION['tries']) >= 5) {
        $error = 'Trop de tentatives. Réessayez dans quelques minutes.';
    } else {
        $st = db()->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();
        if ($row && password_verify($pass, $row['password_hash'])) {
            $_SESSION['tries'] = [];
            login_user((int) $row['id']);
            redirect(home_for($row['role']));
        }
        $_SESSION['tries'][] = time();
        $error = 'E-mail ou mot de passe incorrect.';
    }
}

$pageTitle = 'Connexion';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <h1>Connexion</h1>
    <p class="muted">Accédez à votre espace artiste ou à l'administration.</p>

    <?php if ($error): ?><p class="flash flash-err" role="alert"><?= e($error) ?></p><?php endif; ?>

    <form class="form card" method="post">
        <?= csrf_field() ?>
        <label>Adresse e-mail <input type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required autofocus></label>
        <label>Mot de passe <input type="password" name="password" autocomplete="current-password" required></label>
        <button class="btn" type="submit">Se connecter</button>
    </form>
    <p class="form-foot">Pas encore de compte ? <a href="<?= e(url('inscription.php')) ?>">Rejoignez l'agence</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
