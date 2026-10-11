# Avenue Artiste — site d'agence de booking d'artistes

Site PHP / HTML / CSS en plusieurs fichiers. Base de données SQLite (aucune configuration MySQL nécessaire).

## Installation

1. Envoyez tout le dossier sur un hébergement PHP 8.1 ou plus (extensions `pdo_sqlite` et `fileinfo`, activées par défaut chez la plupart des hébergeurs).
2. Ouvrez `https://votre-site/install.php` et créez le compte administrateur.
3. **Supprimez `install.php`** du serveur une fois le compte créé.
4. Vérifiez que les dossiers `data/` et `uploads/` sont accessibles en écriture par PHP.

En local pour tester : `php -S localhost:8000` depuis ce dossier.

> Serveur Nginx : les fichiers `.htaccess` ne sont pas lus. Bloquez l'accès web à `data/` et `includes/`, et interdisez l'exécution de PHP dans `uploads/`.

## Personnaliser

- **Nom, e-mail, disciplines, types d'évènements** : `includes/config.php`
- **Logo** : `assets/img/logo.svg` (pancarte parisienne noire et dorée, vectorielle, utilisée partout). Pour le remplacer, déposez un autre `logo.svg` (ou `.png` / `.webp`) au même endroit. `favicon.png` est l'icône de l'onglet. L'ancien rendu 3D est conservé dans `logo-original-3d.png` (non utilisé).
- **Couleurs et style** : variables en haut de `assets/css/style.css` (fond `--noir`, surfaces `--encre`, or `--or` / `--or-vif`). Polices : Syne (titres) et Figtree (texte), chargées depuis Google Fonts dans `includes/header.php`.
- **Textes de la page d'accueil** : `index.php`

## Comptes

| Rôle | Peut faire |
|---|---|
| artiste | gérer son profil (photo, bio, liens), accepter ou décliner les propositions |
| booker | valider ou refuser les artistes, créer des évènements, y placer des artistes avec cachet et message |
| admin | tout ce que fait le booker, plus la gestion des rôles et la suppression d'évènements |

Les inscriptions publiques créent toujours des comptes « artiste ». Un admin promeut ensuite les bookers dans l'onglet Comptes.

## Structure

```
index.php  artistes.php  artiste.php  evenements.php
inscription.php  connexion.php  deconnexion.php  install.php
artiste/   index.php (propositions)  profil.php
admin/     index.php  artistes.php  evenements.php  evenement.php  utilisateurs.php
includes/  config.php  db.php  auth.php  functions.php  header.php  footer.php
assets/    css/style.css  js/main.js  img/
data/      base SQLite (créée automatiquement)
uploads/   photos des artistes
```

## Sécurité déjà en place

Mots de passe hachés (`password_hash`), requêtes préparées, protection CSRF sur tous les formulaires, échappement systématique des sorties, vérification du contenu réel des photos envoyées, limitation des tentatives de connexion, cookies de session `HttpOnly` et `SameSite`.

À prévoir avant la mise en ligne publique : HTTPS, mentions légales / CGU / politique de confidentialité, et l'envoi d'e-mails (confirmation d'inscription, notification de proposition), non inclus.
