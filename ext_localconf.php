<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE === 'BE') {


    $hook = ((int)\R3H6\GhostContent\Configuration\ExtensionConfiguration::get('inPageModule') === 1) ? 'drawHeaderHook' : 'drawFooterHook';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php'][$hook][$_EXTKEY] =
            'R3H6\\GhostContent\\Hooks\\PageHook->render';

}
