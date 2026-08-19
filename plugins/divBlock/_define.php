<?php

/**
 * @file
 * @brief       The plugin divBlock definition
 * @ingroup     divBlock
 *
 * @defgroup    divBlock Plugin divBlock.
 *
 * divBlock, adds a "Insert div block" button to the CKEditor post/page editor toolbar.
 *
 * @package     Dotclear
 *
 * @copyright   Pascal Tiberghien
 * @copyright   AGPL-3.0
 */
if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Div Block',                                                            // Name
        'Adds an "Insert div block" button (class/style) to the CKEditor toolbar', // Description
        'Pascal Tiberghien',                                                    // Author
        '0.1',                                                                  // Version
        [
            'permissions' => 'My',
            'type'        => 'plugin',
            'requires'    => [['core', '2.34'], ['dcCKEditor']],
        ]
    );
}
