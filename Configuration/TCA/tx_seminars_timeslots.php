<?php

defined('TYPO3') or die();

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_timeslots',
        'label' => 'begin_date',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'hideTable' => true,
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/TimeSlot.gif',
        'searchFields' => '',
    ],
    'columns' => [
        'seminar' => [
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'begin_date' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_timeslots.begin_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'datetime, required, int',
                'default' => 0,
            ],
        ],
        'end_date' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_timeslots.end_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'datetime, int',
                'default' => 0,
            ],
        ],
        'entry_date' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_timeslots.entry_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'datetime, int',
                'default' => 0,
            ],
        ],
        'speakers' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_timeslots.speakers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_seminars_speakers',
                'foreign_table_where' => 'ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_timeslots_speakers_mm',
            ],
        ],
        'place' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_timeslots.place',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_seminars_sites',
                'foreign_table_where' => 'ORDER BY title',
                'default' => 0,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'items' => [['', '0']],
            ],
        ],
        'room' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_timeslots.room',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'begin_date, end_date, entry_date, speakers, place, room'],
    ],
];

return $tca;
