<?php
require_once __DIR__ . '/includes/auth.php';

$discipline = $_GET['discipline'] ?? '';
$ville = trim($_GET['ville'] ?? '');

$sql = "SELECT id, nom_scene, discipline, ville, photo FROM artistes WHERE statut = 'valide'";
$params = [];
if ($discipline !== '' && in_array($discipline, DISCIPLINES, true)) { $sql .= ' AND discipline = ?'; $params[] = $discipline; } else { $discipline = ''; }
if ($ville !== '') { $sql .= ' AND ville LIKE ?'; $params[] = '%' . $ville . '%'; }
$sql .= ' ORDER BY nom_scene COLLATE NOCASE';
$st = db()->prepare($sql);
$st->execute($params);
$artistes = $st->fetchAll();

$pageTitle = 'Nos artistes';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap">
    <div class="page-head">
        <h1>Nos artistes</h1>
        <p>Des profils validés par l'équipe, prêts pour votre événement.</p>
    </div>

    <div class="chips" style="margin-bottom:1.5rem">
        <a class="chip <?= $discipline === '' ? 'is-active' : '' ?>" href="<?= e(url('artistes.php')) ?>">Tous</a>
        <?php foreach (DISCIPLINES as $d): ?>
            <a class="chip <?= $discipline === $d ? 'is-active' : '' ?>" href="<?= e(url('artistes.php?discipline=' . urlencode($d))) ?>"><?= e($d) ?></a>
        <?php endforeach; ?>
    </div>

    <form class="form" method="get" style="max-width:420px;margin-bottom:2.5rem">
        <?php if ($discipline): ?><input type="hidden" name="discipline" value="<?= e($discipline) ?>"><?php endif; ?>
        <label>Ville
            <input type="text" name="ville" value="<?= e($ville) ?>" placeholder="Paris, Lyon, Marseille…">
        </label>
        <div><button class="btn btn-small" type="submit">Filtrer</button></div>
    </form>

    <?php if ($artistes): ?>
        <div class="artist-grid" style="margin-bottom:5rem">
            <?php foreach ($artistes as $a): ?>
                <a class="artist" href="<?= e(url('artiste.php?id=' . $a['id'])) ?>">
                    <div class="artist-photo">
                        <?php if ($a['photo']): ?><img src="<?= e(url('uploads/' . $a['photo'])) ?>" alt="" loading="lazy"><?php else: ?><?= e(mb_strtoupper(mb_substr($a['nom_scene'], 0, 1))) ?><?php endif; ?>
                    </div>
                    <h3><?= e($a['nom_scene']) ?></h3>
                    <p><?= e($a['discipline']) ?><?= $a['ville'] ? ', ' . e($a['ville']) : '' ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="notice">Aucun artiste ne correspond pour le moment. Essayez une autre discipline ou une autre ville.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
