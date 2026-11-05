<?php
/**
 * @brief devSandbox, un plugin d'exemple pour Dotclear 2
 *
 * Fichier de déclaration du module : c'est le seul fichier lu par Dotclear
 * pour découvrir le plugin. Tout le code vit dans src/ (namespace Dotclear\Plugin\devSandbox).
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Pascal Tits
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Dev Sandbox',                                          // Nom affiché
        'Bac à sable de développement : exemple de plugin',   // Description
        'Pascal Tits',                                          // Auteur
        '0.1.0',                                                // Version (incrémenter pour rejouer Install)
        [
            'date'        => '2026-11-05T00:00:00+0100',
            'requires'    => [['core', '2.39']],   // Version minimale de Dotclear
            'permissions' => 'My',                 // Permissions gérées par My::checkContext()
            'type'        => 'plugin',
            'settings'    => [
                'self' => '',                      // Lien « Configurer » vers la page Manage
            ],
            'details' => 'https://github.com/pascaltits/pascal_dev',
            'support' => 'https://github.com/pascaltits/pascal_dev/issues',
            'license' => 'gpl2',
        ],
    );
}
