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
        'tx_seminars_auxiliary_records_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_auxiliary_records_pid',
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
        'tx_seminars_default_categories' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_default_categories',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_seminars_categories',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_usergroups_categories_mm',
                'wizards' => [
                    'list' => [
                        'type' => 'popup',
                        'title' => 'List entries',
                        'icon' => 'actions-system-list-open',
                        'params' => [
                            'table' => 'tx_seminars_categories',
                            'pid' => '###CURRENT_PID###',
                        ],
                        'module' => [
                            'name' => 'wizard_list',
                        ],
                        'JSopenParams' => 'height=480,width=640,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],
        'tx_seminars_default_organizer' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_default_organizer',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_seminars_organizers',
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
    'tx_seminars_events_pid,' .
    'tx_seminars_auxiliary_records_pid,' .
    'tx_seminars_default_categories, tx_seminars_default_organizer'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'be_groups',
    [
        'tx_seminars_events_folder' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:be_groups.tx_seminars_events_folder',
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
        'tx_seminars_registrations_folder' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:be_groups.tx_seminars_registrations_folder',
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
    'be_groups',
    '--div--;' . 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:be_groups.tab_event_management,' .
    'tx_seminars_events_folder,tx_seminars_registrations_folder'
);
