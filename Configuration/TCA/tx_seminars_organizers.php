<?php
defined('TYPO3_MODE') or die();

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_organizers',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/Organizer.gif',
        'searchFields' => 'title',
    ],
    'interface' => [
        'showRecordFieldList' => 'title,description,homepage,email,email_footer',
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_organizers.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim',
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_organizers.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'homepage' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_organizers.homepage',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'max' => 255,
                'checkbox' => '',
                'eval' => 'trim',
            ],
        ],
        'email' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_organizers.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim,nospace',
            ],
        ],
        'email_footer' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_organizers.email_footer',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'attendances_pid' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_organizers.attendances_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, description, homepage, email, email_footer, attendances_pid'],
    ],
];

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8006000) {
    $tca['columns']['homepage']['config']['renderType'] = 'inputLink';
} else {
    $tca['columns']['description']['defaultExtras'] = 'richtext[]';
    $tca['columns']['homepage']['config']['wizards'] = [
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
    ];
}

return $tca;
