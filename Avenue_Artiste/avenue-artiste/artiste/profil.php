<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_role(['artiste']);
$art = artiste_of((int) $user['id']);
if (!$art) { flash('Profil artiste introuvable.', 'err'); redirect('index.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom_scene  = trim($_POST['nom_scene'] ?? '');
    $discipline = $_POST['discipline'] ?? '';
    $ville      = trim($_POST['ville'] ?? '');
    $bio        = trim($_POST['bio'] ?? '');
    $video      = trim($_POST['video_url'] ?? '');
    $instagram  = ltrim(trim($_POST['instagram'] ?? ''), '@');
    $photo      = $art['photo'];

    if ($nom_scene === '' || mb_strlen($nom_scene) > 80) $errors[] = 'Le nom d\'artiste est obligatoire (80 caractères maximum).';
    if (!in_array($discipline, DISCIPLINES, true)) $errors[] = 'Choisissez une discipline dans la liste.';
    if (mb_strlen($bio) > 2000) $errors[] = 'La bio est limitée à 2000 caractères.';
    if ($video !== '' && !preg_match('#^https?://#i', $video)) $errors[] = 'Le lien vidéo doit commencer par http:// ou https://.';
    if ($instagram !== '' && !preg_match('/^[A-Za-z0-9._]{1,30}$/', $instagram)) $errors[] = 'Le pseudo Instagram n\'est pas valide.';

    // Photo : JPEG, PNG ou WebP, 3 Mo maximum, vérifiée sur le contenu réel du fichier
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['photo'];
        $mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 3 * 1024 * 1024) {
            $errors[] = 'La photo n\'a pas pu être envoyée (3 Mo maximum).';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            if (!isset($mimes[$mime]) || !@getimagesize($f['tmp_name'])) {
                $errors[] = 'Format de photo non accepté. Utilisez JPEG, PNG ou WebP.';
            } else {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                $name = 'a' . $art['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $mimes[$mime];
                if (move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name)) {
                    if ($art['photo'] && is_file(UPLOAD_DIR . $art['photo'])) @unlink(UPLOAD_DIR . $art['photo']);
                    $photo = $name;
                } else {
                    $errors[] = 'Impossible d\'enregistrer la photo sur le serveur.';
                }
            }
        }
    }

    if (!$errors) {
        db()->prepare('UPDATE artistes SET nom_scene=?, discipline=?, ville=?, bio=?, video_url=?, instagram=?, photo=? WHERE id=?')
            ->execute([$nom_scene, $discipline, $ville, $bio, $video, $instagram, $photo, $art['id']]);
        flash('Profil enregistré.');
        redirect('artiste/profil.php');
    }
    $art = array_merge($art, compact('nom_scene', 'discipline', 'ville', 'bio') + ['video_url' => $video, 'instagram' => $instagram]);
}

$pageTitle = 'Mon profil';
require __DIR__ . '/../includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <h1 style="font-size:clamp(2rem,4vw,3rem)">Mon profil</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <p>Statut : <span class="badge <?= e($art['statut']) ?>"><?= e(statut_label($art['statut'])) ?></span></p>
    <?php foreach ($errors as $err): ?><p class="flash flash-err" role="alert"><?= e($err) ?></p><?php endforeach; ?>

    <form class="form card" method="post" enctype="multipart/form-data" style="max-width:720px">
        <?= csrf_field() ?>
        <div class="form-row">
            <label>Nom d'artiste <input type="text" name="nom_scene" value="<?= e($art['nom_scene']) ?>" required></label>
            <label>Discipline
                <select name="discipline" required>
                    <?php foreach (DISCIPLINES as $d): ?><option <?= $art['discipline'] === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?>
                </select>
            </label>
        </div>
        <label>Ville <input type="text" name="ville" value="<?= e($art['ville']) ?>"></label>
        <label>Présentation <small>Votre parcours, vos styles, ce que vous apportez à une soirée.</small>
            <textarea name="bio" maxlength="2000"><?= e($art['bio']) ?></textarea>
        </label>
        <div class="form-row">
            <label>Lien vidéo <small>YouTube, Vimeo…</small> <input type="url" name="video_url" value="<?= e($art['video_url']) ?>" placeholder="https://"></label>
            <label>Instagram <small>Sans le @</small> <input type="text" name="instagram" value="<?= e($art['instagram']) ?>"></label>
        </div>
        <label>Photo <small>JPEG, PNG ou WebP, 3 Mo maximum. Format portrait conseillé.</small>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
        </label>
        <?php if ($art['photo']): ?><img src="<?= e(url('uploads/' . $art['photo'])) ?>" alt="Photo actuelle" style="width:140px;border-radius:12px"><?php endif; ?>
        <div><button class="btn" type="submit">Enregistrer</button></div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
