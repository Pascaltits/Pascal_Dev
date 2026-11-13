<?php
/**
 * Ressources d'aide du plugin (bouton « Aide » de la page d'administration).
 */
declare(strict_types=1);

\Dotclear\App::backend()->resources()
    ->set('help', 'devSandbox', __DIR__ . '/help/devSandbox.html');
