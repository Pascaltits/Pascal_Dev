<?php
/**
 * Configuration Dotclear pour l'environnement de développement Docker.
 *
 * Ce fichier est copié dans inc/config.php au premier démarrage du conteneur.
 * Toutes les valeurs sont lues dans les variables d'environnement (docker-compose.yml / .env),
 * il n'y a donc rien à modifier ici : changer le .env puis `docker compose up -d` suffit.
 */
declare(strict_types=1);

use Dotclear\Helper\File\Path;

if (!defined('DC_RC_PATH')) {
    return;
}

$dc_env = static function (string $name, string $default = ''): string {
    $value = getenv($name);

    return $value === false || $value === '' ? $default : $value;
};
$dc_flag = static fn (string $name, bool $default = false): bool => in_array(strtolower($dc_env($name, $default ? '1' : '0')), ['1', 'true', 'on', 'yes'], true);

// Base de données
define('DC_DBDRIVER', $dc_env('DC_DBDRIVER', 'mysqlimb4'));
define('DC_DBHOST', $dc_env('DC_DBHOST', 'db'));
define('DC_DBUSER', $dc_env('DC_DBUSER', 'dotclear'));
define('DC_DBPASSWORD', $dc_env('DC_DBPASSWORD', 'dotclear'));
define('DC_DBNAME', $dc_env('DC_DBNAME', 'dotclear'));
define('DC_DBPREFIX', $dc_env('DC_DBPREFIX', 'dc_'));
define('DC_DBPERSIST', false);

// Clé de chiffrement des mots de passe
define('DC_MASTER_KEY', $dc_env('DC_MASTER_KEY', 'dev-master-key-change-me'));

// URL d'administration et expéditeur des mails
define('DC_ADMIN_URL', rtrim($dc_env('DC_PUBLIC_URL', 'http://localhost:8080'), '/') . '/admin/');
define('DC_ADMIN_MAILFROM', $dc_env('DC_ADMIN_MAILFROM', 'dotclear@localhost'));

// Session
define('DC_SESSION_NAME', 'dcxd');

// Plugins : ceux livrés avec Dotclear + ceux du dépôt (montés dans /workspace/plugins)
define('DC_PLUGINS_ROOT', __DIR__ . '/../plugins' . PATH_SEPARATOR . $dc_env('DC_DEV_PLUGINS_ROOT', '/workspace/plugins'));

// Caches et répertoire var
define('DC_TPL_CACHE', Path::real(__DIR__ . '/..') . '/cache');
define('DC_VAR', Path::real(__DIR__ . '/..') . '/var');

// Algorithme de hachage
define('DC_CRYPT_ALGO', 'sha512');

// Mode développement : messages d'erreur détaillés, JS non minifié
define('DC_DEBUG', $dc_flag('DC_DEBUG', true));
define('DC_DEV', $dc_flag('DC_DEV', true));

// Pas de vérification de mise à jour du cœur en développement
define('DC_NOT_UPDATE', true);

// Derrière un reverse proxy (Codespaces, Traefik…) qui envoie X-Forwarded-Proto
define('DC_REVERSE_PROXY', $dc_flag('DC_REVERSE_PROXY', false));

unset($dc_env, $dc_flag);
