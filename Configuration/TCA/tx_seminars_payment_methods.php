<?php
defined('TYPO3_MODE') or die();

return [
    'ctrl' => [
        'title' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_payment_methods',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/PaymentMethod.gif',
        'searchFields' => 'title'
    ],
    'interface' => [
        'showRecordFieldList' => 'title, description',
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_payment_methods.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_payment_methods.description',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '10',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title;;;;2-2-2, description'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];
