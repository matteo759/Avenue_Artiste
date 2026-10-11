<?php
require_once __DIR__ . '/config.php';

// Solutions de repli si l'extension mbstring n'est pas activée chez l'hébergeur
if (!function_exists('mb_strlen'))      { function mb_strlen(string $s): int { return (int) preg_match_all('/./us', $s); } }
if (!function_exists('mb_substr'))      { function mb_substr(string $s, int $start, ?int $len = null): string { return implode('', array_slice(preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [], $start, $len)); } }
if (!function_exists('mb_strtoupper'))  { function mb_strtoupper(string $s): string { return strtoupper($s); } }
if (!function_exists('mb_strtolower'))  { function mb_strtolower(string $s): string { return strtolower($s); } }

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }

function flash(?string $msg = null, string $type = 'ok'): ?array {
    if ($msg !== null) { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
    if (!$ok) { http_response_code(419); exit('Session expirée. Recharge la page et réessaie.'); }
}

function date_fr(?string $d): string {
    if (!$d) return '';
    $t = strtotime($d);
    $mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    return date('j', $t) . ' ' . $mois[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
}

function statut_label(string $s): string {
    return [
        'en_attente' => 'En attente', 'valide' => 'Validé', 'refuse' => 'Refusé',
        'propose' => 'Proposé', 'accepte' => 'Accepté', 'decline' => 'Décliné',
        'ouvert' => 'Ouvert', 'complet' => 'Complet', 'annule' => 'Annulé',
    ][$s] ?? $s;
}
