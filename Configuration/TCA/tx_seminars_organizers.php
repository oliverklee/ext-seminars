<?php
defined('TYPO3_MODE') or die();

return [
    'ctrl' => [
        'title' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_organizers',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        // We cannot use the EXT:seminars syntax as this would break getIcon::getIcon (which gets called in
        // OldModel/Abstract::getRecordIcon where the icons for the BE module are created).
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('seminars') . 'Resources/Public/Icons/Organizer.gif',
        'searchFields' => 'title'
    ],
    'interface' => [
        'showRecordFieldList' => 'title,description,homepage,email,email_footer'
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_organizers.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_organizers.description',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            ],
        ],
        'homepage' => [
            'exclude' => 1,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_organizers.homepage',
            'config' => [
                'type' => 'input',
                'size' => '15',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'link_popup.gif',
                        'module' => array(
                            'name' => 'wizard_element_browser',
                            'urlParameters' => array(
                                'mode' => 'wizard',
                            ),
                        ),
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],
        'email' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_organizers.email',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim,nospace',
            ],
        ],
        'email_footer' => [
            'exclude' => 1,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_organizers.email_footer',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            ],
        ],
        'attendances_pid' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_organizers.attendances_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => '1',
                'maxitems' => '1',
                'minitems' => '0',
                'show_thumbs' => '1',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title;;;;2-2-2, description;;;richtext[paste|bold|italic|orderedlist|unorderedlist|link]:rte_transform[mode=ts_css], homepage;;;;3-3-3, email, email_footer, attendances_pid'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];
