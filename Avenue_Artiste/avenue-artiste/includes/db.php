<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0750, true);

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'artiste' CHECK (role IN ('artiste','booker','admin')),
        nom TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS artistes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
        nom_scene TEXT NOT NULL,
        discipline TEXT NOT NULL,
        ville TEXT,
        bio TEXT,
        video_url TEXT,
        instagram TEXT,
        photo TEXT,
        statut TEXT NOT NULL DEFAULT 'en_attente' CHECK (statut IN ('en_attente','valide','refuse')),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS evenements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre TEXT NOT NULL,
        type TEXT NOT NULL,
        date_event TEXT NOT NULL,
        lieu TEXT,
        ville TEXT,
        description TEXT,
        statut TEXT NOT NULL DEFAULT 'ouvert' CHECK (statut IN ('ouvert','complet','annule')),
        created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS affectations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        evenement_id INTEGER NOT NULL REFERENCES evenements(id) ON DELETE CASCADE,
        artiste_id INTEGER NOT NULL REFERENCES artistes(id) ON DELETE CASCADE,
        statut TEXT NOT NULL DEFAULT 'propose' CHECK (statut IN ('propose','accepte','decline')),
        cachet INTEGER,
        note TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (evenement_id, artiste_id)
    );
    ");
    return $pdo;
}
