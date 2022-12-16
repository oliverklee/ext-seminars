<?php

defined('TYPO3') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    [
        'tx_seminars_registration' => [
            'exclude' => 1,
            'label' => 'registration (not visible in the BE)',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_event_types',
                'default' => 0,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_groups',
    [
        'tx_seminars_events_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_events_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'default' => 0,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_groups',
    '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tab_event_management,' .
    'tx_seminars_events_pid'
);
