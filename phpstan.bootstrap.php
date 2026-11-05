<?php
/**
 * Amorce phpstan : constantes définies à l'exécution par Dotclear.
 */
declare(strict_types=1);

if (!defined('DC_RC_PATH')) {
    define('DC_RC_PATH', __DIR__ . '/dotclear/inc/config.php');
}
if (!defined('DC_DEBUG')) {
    define('DC_DEBUG', true);
}
if (!defined('DC_DEV')) {
    define('DC_DEV', true);
}
