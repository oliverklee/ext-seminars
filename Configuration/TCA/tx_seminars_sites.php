<?php
defined('TYPO3_MODE') or die();

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/Place.gif',
        'searchFields' => 'title',
    ],
    'interface' => [
        'showRecordFieldList' => 'title,address,zip,city,homepage,directions,notes,owner',
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim',
            ],
        ],
        'address' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.address',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'zip' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.zip',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'city' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim',
            ],
        ],
        'country' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.country',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'itemsProcFunc' => \OliverKlee\Seminars\BackEnd\TceForms::class . '->createCountrySelector',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'homepage' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.homepage',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'max' => 255,
                'checkbox' => '',
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'actions-wizard-link',
                        'module' => [
                            'name' => 'wizard_link',
                            'urlParameters' => [
                                'mode' => 'wizard',
                            ],
                        ],
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],
        'directions' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.directions',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'notes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_sites.notes',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'owner' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:owner_feuser',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, address, zip, city, country, homepage, directions, notes, owner'],
    ],
];

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8006000) {
    $tca['columns']['homepage']['renderType'] = 'inputLink';
} else {
    $tca['columns']['directions']['defaultExtras'] = 'richtext[]';
}

return $tca;
