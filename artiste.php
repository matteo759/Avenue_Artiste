<?php
require_once __DIR__ . '/includes/auth.php';

$st = db()->prepare("SELECT * FROM artistes WHERE id = ? AND statut = 'valide'");
$st->execute([(int) ($_GET['id'] ?? 0)]);
$a = $st->fetch();
if (!$a) { http_response_code(404); $pageTitle = 'Artiste introuvable'; require __DIR__ . '/includes/header.php'; echo '<div class="wrap page-head"><h1>Artiste introuvable</h1><p><a href="' . e(url('artistes.php')) . '">Retour à la liste des artistes</a></p></div>'; require __DIR__ . '/includes/footer.php'; exit; }

// Seuls les liens http(s) sont affichés
$safeUrl = fn(?string $u) => ($u && preg_match('#^https?://#i', $u)) ? $u : null;
$video = $safeUrl($a['video_url']);
$insta = $a['instagram'] ? ltrim($a['instagram'], '@') : null;

$pageTitle = $a['nom_scene'];
require __DIR__ . '/includes/header.php';
?>
<div class="wrap" style="padding-block:3rem 5rem">
    <div class="profile">
        <div class="artist-photo">
            <?php if ($a['photo']): ?><img src="<?= e(url('uploads/' . $a['photo'])) ?>" alt="Photo de <?= e($a['nom_scene']) ?>"><?php else: ?><?= e(mb_strtoupper(mb_substr($a['nom_scene'], 0, 1))) ?><?php endif; ?>
        </div>
        <div>
            <h1><?= e($a['nom_scene']) ?></h1>
            <div class="profile-meta">
                <span class="tag"><?= e($a['discipline']) ?></span>
                <?php if ($a['ville']): ?><span class="tag"><?= e($a['ville']) ?></span><?php endif; ?>
            </div>
            <?php if ($a['bio']): ?><p><?= nl2br(e($a['bio'])) ?></p><?php endif; ?>
            <p>
                <?php if ($video): ?><a class="btn btn-small" href="<?= e($video) ?>" target="_blank" rel="noopener noreferrer">Voir une vidéo</a><?php endif; ?>
                <?php if ($insta): ?><a class="btn btn-small btn-line" href="https://instagram.com/<?= e(rawurlencode($insta)) ?>" target="_blank" rel="noopener noreferrer">Instagram</a><?php endif; ?>
            </p>
            <p class="muted">Pour réserver cet artiste, écrivez-nous à <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>.</p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
