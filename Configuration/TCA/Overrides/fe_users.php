<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die('Access denied.');

ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    [
        'tx_seminars_registration' => [
            'exclude' => 1,
            'label' => 'registration (not visible in the BE)',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_seminars_event_types',
                'default' => 0,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ]
);
