<?php
defined('TYPO3_MODE') or die('Access denied.');

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
        'tx_seminars_publish_events' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_publish_events',
            'config' => [
                'type' => 'radio',
                'default' => '0',
                'items' => [
                    ['LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_publish_events.I.0', '0'],
                    ['LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_publish_events.I.1', '1'],
                    ['LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_publish_events.I.2', '2'],
                ],
            ],
        ],
        'tx_seminars_events_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_events_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
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
                'size' => '1',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'tx_seminars_reviewer' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_reviewer',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'be_users',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'tx_seminars_default_categories' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_seminars_default_categories',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_categories',
                'foreign_table' => 'tx_seminars_categories',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_usergroups_categories_mm',
                'wizards' => [
                    'list' => [
                        'type' => 'popup',
                        'title' => 'List entries',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif',
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
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectSingle',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_organizers',
                'foreign_table' => 'tx_seminars_organizers',
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
    'tx_seminars_publish_events,tx_seminars_events_pid,' .
    'tx_seminars_auxiliary_records_pid,tx_seminars_reviewer,' .
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
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'tx_seminars_auxiliaries_folder' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:be_groups.tx_seminars_auxiliaries_folder',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => '1',
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'be_groups',
    '--div--;' . 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:be_groups.tab_event_management,' .
    'tx_seminars_events_folder,tx_seminars_registrations_folder,' .
    'tx_seminars_auxiliaries_folder,'
);
