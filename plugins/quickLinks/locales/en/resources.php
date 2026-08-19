<?php

declare(strict_types=1);

/**
 * @file
 * @brief       The module backend helper resource
 * @ingroup     quickLinks
 *
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
\Dotclear\App::backend()->resources()->set('help', 'quickLinks', __DIR__ . '/help/help.html');
