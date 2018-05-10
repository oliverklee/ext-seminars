<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_seminars_speakers');

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile'  => 'EXT:seminars/Resources/Public/Icons/Speaker.gif',
        'searchFields' => 'title',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,organization,homepage,description,skills,notes,address,phone_work,phone_home,phone_mobile,fax,email,cancelation_period,owner',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim',
            ],
        ],
        'gender' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.gender',
            'config' => [
                'type' => 'radio',
                'default' => '0',
                'items' => [
                    ['', '0'],
                    ['LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.gender_male', '1'],
                    ['LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.gender_female', '2'],
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'organization' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.organization',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'homepage' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.homepage',
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
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'enableRichtext' => true,
            ],
        ],
        'skills' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.skills',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_skills',
                'foreign_table' => 'tx_seminars_skills',
                'foreign_table_where' => 'ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_speakers_skills_mm',
                'wizards' => \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
                    \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
                    'tx_seminars_skills'
                ),
            ],
        ],
        'notes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.notes',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'address' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.address',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'phone_work' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.phone_work',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'phone_home' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.phone_home',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'phone_mobile' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.phone_mobile',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'fax' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.fax',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,nospace',
            ],
        ],
        'cancelation_period' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_speakers.cancelation_period',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'max' => 3,
                'eval' => 'num',
                'checkbox' => '0',
                'range' => [
                    'upper' => 999,
                    'lower' => 0,
                ],
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
        '0' => ['showitem' => 'hidden, title, gender, organization, homepage, description, skills, notes, address, phone_work, phone_home, phone_mobile, fax, email, cancelation_period, owner'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8006000) {
    $tca['columns']['homepage']['renderType'] = 'inputLink';
} else {
    $tca['columns']['description']['defaultExtras'] = 'richtext[]';
}

return $tca;
