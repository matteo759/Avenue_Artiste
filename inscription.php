<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) redirect(home_for(current_user()['role']));

$errors = [];
$v = ['nom' => '', 'email' => '', 'nom_scene' => '', 'discipline' => '', 'ville' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($v as $k => $_) { $v[$k] = trim($_POST[$k] ?? ''); }
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($v['nom'] === '' || mb_strlen($v['nom']) > 100) $errors[] = 'Indiquez votre nom (100 caractères maximum).';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Cette adresse e-mail n\'est pas valide.';
    if ($v['nom_scene'] === '' || mb_strlen($v['nom_scene']) > 80) $errors[] = 'Indiquez votre nom d\'artiste (80 caractères maximum).';
    if (!in_array($v['discipline'], DISCIPLINES, true)) $errors[] = 'Choisissez une discipline dans la liste.';
    if (mb_strlen($v['ville']) > 80) $errors[] = 'Le nom de la ville est trop long.';
    if (strlen($pass) < 10) $errors[] = 'Le mot de passe doit contenir au moins 10 caractères.';
    if ($pass !== $pass2) $errors[] = 'Les deux mots de passe ne sont pas identiques.';

    if (!$errors) {
        $pdo = db();
        $st = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $st->execute([mb_strtolower($v['email'])]);
        if ($st->fetch()) {
            $errors[] = 'Un compte existe déjà avec cette adresse. Connectez-vous ou utilisez une autre adresse.';
        } else {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO users (email, password_hash, role, nom) VALUES (?, ?, \'artiste\', ?)')
                ->execute([mb_strtolower($v['email']), password_hash($pass, PASSWORD_DEFAULT), $v['nom']]);
            $uid = (int) $pdo->lastInsertId();
            $pdo->prepare('INSERT INTO artistes (user_id, nom_scene, discipline, ville) VALUES (?, ?, ?, ?)')
                ->execute([$uid, $v['nom_scene'], $v['discipline'], $v['ville']]);
            $pdo->commit();

            login_user($uid);
            flash('Bienvenue ! Complétez votre profil : l\'équipe le validera sous peu.');
            redirect('artiste/profil.php');
        }
    }
}

$pageTitle = 'Rejoindre l\'agence';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <h1>Rejoindre l'agence</h1>
    <p class="muted">Créez votre compte artiste. Il faut deux minutes.</p>

    <?php foreach ($errors as $err): ?><p class="flash flash-err" role="alert"><?= e($err) ?></p><?php endforeach; ?>

    <form class="form card" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-row">
            <label>Votre nom <input type="text" name="nom" value="<?= e($v['nom']) ?>" autocomplete="name" required></label>
            <label>Nom d'artiste <input type="text" name="nom_scene" value="<?= e($v['nom_scene']) ?>" required></label>
        </div>
        <div class="form-row">
            <label>Discipline
                <select name="discipline" required>
                    <option value="">Choisir…</option>
                    <?php foreach (DISCIPLINES as $d): ?><option <?= $v['discipline'] === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label>Ville <input type="text" name="ville" value="<?= e($v['ville']) ?>" autocomplete="address-level2"></label>
        </div>
        <label>Adresse e-mail <input type="email" name="email" value="<?= e($v['email']) ?>" autocomplete="email" required></label>
        <div class="form-row">
            <label>Mot de passe <small>10 caractères minimum</small> <input type="password" name="password" autocomplete="new-password" required></label>
            <label>Confirmation <small>&nbsp;</small> <input type="password" name="password2" autocomplete="new-password" required></label>
        </div>
        <button class="btn" type="submit">Créer mon compte</button>
    </form>
    <p class="form-foot">Déjà inscrit ? <a href="<?= e(url('connexion.php')) ?>">Connectez-vous</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
