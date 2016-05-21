<?php
defined('TYPO3_MODE') or die();

return [
    'ctrl' => [
        'title' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_test',
        'readOnly' => 1,
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY uid',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/Test.gif',
        'searchFields' => 'title',
    ],
    $GLOBALS['TCA']['tx_seminars_test'] = [
        'interface' => [
            'showRecordFieldList' => 'hidden,starttime,endtime,title',
        ],
        'columns' => [
            'hidden' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
                'config' => [
                    'type' => 'check',
                    'default' => '0',
                ],
            ],
            'starttime' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
                'config' => [
                    'type' => 'none',
                    'size' => '8',
                    'max' => '20',
                    'eval' => 'date',
                    'default' => '0',
                    'checkbox' => '0',
                ],
            ],
            'endtime' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
                'config' => [
                    'type' => 'none',
                    'size' => '8',
                    'max' => '20',
                    'eval' => 'date',
                    'checkbox' => '0',
                    'default' => '0',
                    'range' => [
                        'upper' => mktime(0, 0, 0, 12, 31, 2020),
                        'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')),
                    ],
                ],
            ],
            'title' => [
                'exclude' => 0,
                'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_test.title',
                'config' => [
                    'type' => 'none',
                    'size' => '30',
                ],
            ],
        ],
        'types' => [
            '0' => ['showitem' => 'title;;;;2-2-2'],
        ],
        'palettes' => [
            '1' => ['showitem' => 'starttime, endtime'],
        ],
    ],
];
