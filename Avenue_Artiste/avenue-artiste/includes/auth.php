<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

function current_user(): ?array {
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['uid'])) {
            $st = db()->prepare('SELECT id, email, nom, role FROM users WHERE id = ?');
            $st->execute([$_SESSION['uid']]);
            $user = $st->fetch() ?: null;
        }
    }
    return $user;
}

function login_user(int $id): void {
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
}

function logout_user(): void {
    $_SESSION = [];
    session_destroy();
}

function home_for(string $role): string {
    return $role === 'artiste' ? 'artiste/index.php' : 'admin/index.php';
}

function require_login(): array {
    $u = current_user();
    if (!$u) { flash('Connecte-toi pour accéder à cet espace.', 'err'); redirect('connexion.php'); }
    return $u;
}

/** @param string[] $roles */
function require_role(array $roles): array {
    $u = require_login();
    if (!in_array($u['role'], $roles, true)) { http_response_code(403); exit('Accès refusé.'); }
    return $u;
}

function artiste_of(int $userId): ?array {
    $st = db()->prepare('SELECT * FROM artistes WHERE user_id = ?');
    $st->execute([$userId]);
    return $st->fetch() ?: null;
}
