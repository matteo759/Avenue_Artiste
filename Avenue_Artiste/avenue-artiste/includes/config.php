<?php
// ---------- Réglages du site : modifie ces valeurs ----------
const SITE_NAME    = 'Avenue Artiste';
const SITE_TAGLINE = 'Agence de booking pour artistes';
const SITE_EMAIL   = 'contact@exemple.fr';

// Base de données SQLite (créée automatiquement au premier chargement)
const DB_PATH    = __DIR__ . '/../data/app.sqlite';
const UPLOAD_DIR = __DIR__ . '/../uploads/';

// Disciplines proposées à l'inscription
const DISCIPLINES = [
    'DJ', 'Saxophoniste', 'Groupe live', 'Chanteur / Chanteuse', 'Violoniste',
    'Danseur / Danseuse', 'Percussionniste', 'Animateur / MC', 'Autre',
];

const TYPES_EVENEMENT = [
    'Mariage', 'Soirée d\'entreprise', 'Club / Bar', 'Festival',
    'Anniversaire', 'Hôtel / Rooftop', 'Soirée privée', 'Autre',
];

// URL de base calculée automatiquement (fonctionne à la racine ou dans un sous-dossier)
$root = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$doc  = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
define('BASE_URL', rtrim(str_replace($doc, '', $root), '/'));
