<?php

/**
 * @file
 * @brief       The plugin quickLinks definition
 * @ingroup     quickLinks
 *
 * @defgroup    quickLinks Plugin quickLinks.
 *
 * quickLinks, manage a custom list of quick links from the backend.
 *
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Quick Links',                                    // Name
        'Manage a custom list of quick links per blog',   // Description
        'Pascal Tiberghien',                              // Author
        '0.1',                                            // Version
        [
            'permissions' => 'My',
            'type'        => 'plugin',
            'requires'    => [['core', '2.34']],
            'settings'    => [
                'self' => '',
            ],
        ]
    );
}
