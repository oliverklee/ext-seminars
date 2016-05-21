<?php
defined('TYPO3_MODE') or die();

return [
    'ctrl' => [
        'title' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_target_groups',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/TargetGroup.gif',
        'searchFields' => 'title',
    ],
    'interface' => [
        'showRecordFieldList' => 'title,minimum_age,maximum_age,owner',
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_target_groups.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            ],
        ],
        'minimum_age' => [
            'exclude' => 1,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_target_groups.minimum_age',
            'config' => [
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
                'range' => [
                    'lower' => '0',
                    'upper' => '199',
                ],
            ],
        ],
        'maximum_age' => [
            'exclude' => 1,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_target_groups.maximum_age',
            'config' => [
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
                'range' => [
                    'lower' => '0',
                    'upper' => '199',
                ],
            ],
        ],
        'owner' => [
            'exclude' => 1,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'owner_feuser',
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
        '0' => ['showitem' => 'title;;;;2-2-2, minimum_age, maximum_age, owner'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];
