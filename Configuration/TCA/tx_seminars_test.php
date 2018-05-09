<?php
defined('TYPO3_MODE') or die();

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_test',
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
    'interface' => [
        'showRecordFieldList' => 'hidden,starttime,endtime,title',
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
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'none',
                'size' => 8,
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0',
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'none',
                'size' => 8,
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
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_test.title',
            'config' => [
                'type' => 'none',
                'size' => 30,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title'],
    ],
    'palettes' => [
        '1' => ['showitem' => 'starttime, endtime'],
    ],
];

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8006000) {
    $tca['columns']['starttime']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['endtime']['config']['renderType'] = 'inputDateTime';
}

return $tca;
