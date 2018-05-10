<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_seminars_seminars');

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars',
        'label' => 'title',
        'type' => 'object_type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY begin_date DESC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/EventComplete.gif',
        'typeicon_column' => 'object_type',
        'typeicon_classes' => [
            'default' => 'tx-seminars-event-complete',
            '0' => 'tx-seminars-event-complete',
            '1' => 'tx-seminars-event-topic',
            '2' => 'tx-seminars-event-date',
        ],
        'dividers2tabs' => true,
        'hideAtCopy' => true,
        'searchFields' => 'title,accreditation_number',
    ],
    'interface' => [
        'showRecordFieldList' => 'title,subtitle,categories,teaser,description,accreditation_number,credit_points,begin_date,end_date,time_zone,timeslots,begin_date_registration,deadline_registration,deadline_unregistration,expiry,details_page,place,room,speakers,price_regular,price_special,payment_methods,organizers,organizing_partners,event_takes_place_reminder_sent,cancelation_deadline_reminder_sent,needs_registration,allows_multiple_registrations,attendees_min,attendees_max,queue_size,offline_attendees,organizers_notified_about_minimum_reached,mute_notification_emails,target_groups,skip_collision_check,registrations,cancelled,automatic_confirmation_cancelation,notes,attached_files,hidden,starttime,endtime,owner_feuser,vips,price_on_request,date_of_last_registration_digest',
    ],
    'columns' => [
        'object_type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.object_type',
            'config' => [
                'type' => 'radio',
                'default' => '0',
                'items' => [
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.object_type.I.0',
                        '0',
                    ],
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.object_type.I.1',
                        '1',
                    ],
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.object_type.I.2',
                        '2',
                    ],
                ],
            ],
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim',
            ],
        ],
        'topic' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.topic',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectSingle',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_seminars',
                'foreign_table' => 'tx_seminars_seminars',
                // only allow for topic records and complete event records, but not for date records
                'foreign_table_where' => 'AND (tx_seminars_seminars.object_type = 0 OR tx_seminars_seminars.object_type = 1) ORDER BY title',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'subtitle' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.subtitle',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'categories' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.categories',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_categories',
                'foreign_table' => 'tx_seminars_categories',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_categories_mm',
            ],
        ],
        'requirements' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.requirements',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_seminars',
                'foreign_table' => 'tx_seminars_seminars',
                'foreign_table_where' => 'AND tx_seminars_seminars.uid <> ###THIS_UID### AND object_type = 1 ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_requirements_mm',
            ],
        ],
        'dependencies' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.dependencies',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'foreign_table' => 'tx_seminars_seminars',
                'foreign_table_where' => 'AND tx_seminars_seminars.uid <> ###THIS_UID### AND object_type = 1 ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_requirements_mm',
                'MM_opposite_field' => 'requirements',
            ],
        ],
        'teaser' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.teaser',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'event_type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.event_type',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectSingle',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_event_types',
                'foreign_table' => 'tx_seminars_event_types',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'items' => [['', '0']],
            ],
        ],
        'accreditation_number' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.accreditation_number',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],
        'credit_points' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.credit_points',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'max' => 3,
                'eval' => 'int',
                'checkbox' => '0',
                'range' => [
                    'upper' => 999,
                    'lower' => 0,
                ],
                'default' => 0,
            ],
        ],
        'begin_date' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.begin_date',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'datetime',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
        'end_date' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.end_date',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'datetime',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
        'time_zone' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.time_zone',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'timeslots' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.timeslots',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_seminars_timeslots',
                'foreign_field' => 'seminar',
                'foreign_default_sortby' => 'tx_seminars_timeslots.begin_date',
                'maxitems' => 999,
                'appearance' => [
                    'levelLinksPosition' => 'bottom',
                    'expandSingle' => 1,
                ],
            ],
        ],
        'begin_date_registration' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.begin_date_registration',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'datetime',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
        'deadline_registration' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.deadline_registration',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'datetime',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
        'deadline_early_bird' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.deadline_early_bird',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'datetime',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
        'deadline_unregistration' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.deadline_unregistration',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'datetime',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
        'expiry' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.expiry',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
        'details_page' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.details_page',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'max' => 255,
                'checkbox' => '',
                'eval' => 'trim',
            ],
        ],
        'place' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.place',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_sites',
                'foreign_table' => 'tx_seminars_sites',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_place_mm',
            ],
        ],
        'room' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.room',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'lodgings' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.lodgings',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_lodgings',
                'foreign_table' => 'tx_seminars_lodgings',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_lodgings_mm',
            ],
        ],
        'foods' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.foods',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_foods',
                'foreign_table' => 'tx_seminars_foods',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_foods_mm',
            ],
        ],
        'speakers' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.speakers',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_speakers',
                'foreign_table' => 'tx_seminars_speakers',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_speakers_mm',
            ],
        ],
        'partners' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.partners',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_speakers',
                'foreign_table' => 'tx_seminars_speakers',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_speakers_mm_partners',
            ],
        ],
        'tutors' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.tutors',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_speakers',
                'foreign_table' => 'tx_seminars_speakers',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_speakers_mm_tutors',
            ],
        ],
        'leaders' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.leaders',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_speakers',
                'foreign_table' => 'tx_seminars_speakers',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_speakers_mm_leaders',
            ],
        ],
        'language' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \OliverKlee\Seminars\BackEnd\TceForms::class . '->createLanguageSelector',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'price_regular' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.price_regular',
            'displayCond' => 'FIELD:price_on_request:REQ:false',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'double2',
                'checkbox' => '0.00',
                'range' => [
                    'upper' => '999999.99',
                    'lower' => '0.00',
                ],
                'default' => '0.00',
            ],
        ],
        'price_regular_early' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.price_regular_early',
            'displayCond' => [
                'AND' => [
                    'FIELD:needs_registration:REQ:true',
                    'FIELD:price_on_request:REQ:false',
                ],
            ],
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'double2',
                'checkbox' => '0.00',
                'range' => [
                    'upper' => '999999.99',
                    'lower' => '0.00',
                ],
                'default' => '0.00',
            ],
        ],
        'price_regular_board' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.price_regular_board',
            'displayCond' => 'FIELD:price_on_request:REQ:false',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'double2',
                'checkbox' => '0.00',
                'range' => [
                    'upper' => '999999.99',
                    'lower' => '0.00',
                ],
                'default' => '0.00',
            ],
        ],
        'price_special' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.price_special',
            'displayCond' => 'FIELD:price_on_request:REQ:false',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'double2',
                'checkbox' => '0.00',
                'range' => [
                    'upper' => '999999.99',
                    'lower' => '0.00',
                ],
                'default' => '0.00',
            ],
        ],
        'price_special_early' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.price_special_early',
            'displayCond' => [
                'AND' => [
                    'FIELD:needs_registration:REQ:true',
                    'FIELD:price_on_request:REQ:false',
                ],
            ],
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'double2',
                'checkbox' => '0.00',
                'range' => [
                    'upper' => '999999.99',
                    'lower' => '0.00',
                ],
                'default' => '0.00',
            ],
        ],
        'price_special_board' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.price_special_board',
            'displayCond' => 'FIELD:price_on_request:REQ:false',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'double2',
                'checkbox' => '0.00',
                'range' => [
                    'upper' => '999999.99',
                    'lower' => '0.00',
                ],
                'default' => '0.00',
            ],
        ],
        'additional_information' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.additional_information',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'checkboxes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.checkboxes',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_checkboxes',
                'foreign_table' => 'tx_seminars_checkboxes',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_checkboxes_mm',
            ],
        ],
        'uses_terms_2' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.uses_terms_2',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'payment_methods' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.payment_methods',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_payment_methods',
                'foreign_table' => 'tx_seminars_payment_methods',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_payment_methods_mm',
            ],
        ],
        'organizers' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.organizers',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_organizers',
                'foreign_table' => 'tx_seminars_organizers',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 5,
                'minitems' => 1,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_organizers_mm',
            ],
        ],
        'organizing_partners' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.organizing_partners',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_organizers',
                'foreign_table' => 'tx_seminars_organizers',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_organizing_partners_mm',
            ],
        ],
        'event_takes_place_reminder_sent' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.event_takes_place_reminder_sent',
            'config' => [
                'type' => 'check',
                'readOnly' => 1,
            ],
        ],
        'cancelation_deadline_reminder_sent' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.cancelation_deadline_reminder_sent',
            'config' => [
                'type' => 'check',
                'readOnly' => 1,
            ],
        ],
        'needs_registration' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.needs_registration',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'allows_multiple_registrations' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.allows_multiple_registrations',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'attendees_min' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.attendees_min',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'max' => 4,
                'eval' => 'int',
                'checkbox' => '0',
                'range' => [
                    'upper' => 9999,
                    'lower' => 0,
                ],
                'default' => 0,
            ],
        ],
        'attendees_max' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.attendees_max',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'max' => 4,
                'eval' => 'int',
                'checkbox' => '0',
                'range' => [
                    'upper' => 9999,
                    'lower' => 0,
                ],
                'default' => 0,
            ],
        ],
        'queue_size' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.queue_size',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'offline_attendees' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.offline_attendees',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'max' => 3,
                'eval' => 'int',
                'checkbox' => '0',
                'range' => [
                    'upper' => 999,
                    'lower' => 0,
                ],
                'default' => 0,
            ],
        ],
        'organizers_notified_about_minimum_reached' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.organizers_notified_about_minimum_reached',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'mute_notification_emails' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.mute_notification_emails',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'target_groups' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.target_groups',
            'config' => [
                'type' => \OliverKlee\Seminars\BackEnd\TceForms::getSelectType(),
                'renderType' => 'selectMultipleSideBySide',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_target_groups',
                'foreign_table' => 'tx_seminars_target_groups',
                'foreign_table_where' => ' ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_target_groups_mm',
            ],
        ],
        'skip_collision_check' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.skip_collision_check',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'registrations' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.registrations',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_seminars_attendances',
                'foreign_field' => 'seminar',
                'foreign_default_sortby' => 'tx_seminars_attendances.crdate',
                'maxitems' => 999,
                'appearance' => [
                    'levelLinksPosition' => 'bottom',
                    'expandSingle' => 1,
                ],
            ],
        ],
        'cancelled' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.cancelled',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => '0',
                'items' => [
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.cancelled_planned',
                        '0',
                    ],
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.cancelled_canceled',
                        '1',
                    ],
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.cancelled_confirmed',
                        '2',
                    ],
                ],

            ],
        ],
        'automatic_confirmation_cancelation' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.automatic_confirmation_cancelation',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'notes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.notes',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'attached_files' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.attached_files',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'jpg,jpeg,png,bmp,gif,tiff,tif,' . 'txt,pdf,ps,' .
                    'svg,' . 'doc,docx,sxw,odt,' . 'xls,xlsx,sxc,ods,' .
                    'ppt,pptx,sxi,odp,' . 'html,htm,css,js,xml,xsd,' .
                    'zip,rar,gz,tgz,tar,bz2,tbz,tbz2',
                'max_size' => 4096,
                'uploadfolder' => 'uploads/tx_seminars/',
                'size' => 5,
                'maxitems' => 200,
                'minitems' => 0,
                'autoSizeMax' => 40,
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0',
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
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
        'owner_feuser' => [
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
        'vips' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.vips',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_seminars_feusers_mm',
            ],
        ],
        'image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.image',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'gif,png,jpeg,jpg',
                'max_size' => 4096,
                'uploadfolder' => 'uploads/tx_seminars',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'publication_hash' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.publication_hash',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'price_on_request' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.price_on_request',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'date_of_last_registration_digest' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.date_of_last_registration_digest',
            'displayCond' => 'FIELD:needs_registration:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'datetime',
                'checkbox' => '0',
                'default' => '0',
            ],
        ],
    ],
    'types' => [
        // Single event
        '0' => [
            'showitem' => '' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelGeneral, object_type, title, subtitle, image, categories, teaser, description, event_type, language, accreditation_number, credit_points, details_page, additional_information, checkboxes, uses_terms_2, cancelled, automatic_confirmation_cancelation, notes, attached_files, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelPlaceTime, begin_date, end_date, time_zone, timeslots, begin_date_registration, deadline_registration, deadline_early_bird, deadline_unregistration, place, room, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelSpeakers, speakers, partners, tutors, leaders, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelOrganizers, organizers, organizing_partners, event_takes_place_reminder_sent, cancelation_deadline_reminder_sent, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelAttendees, needs_registration, allows_multiple_registrations, attendees_min, attendees_max, queue_size, offline_attendees, organizers_notified_about_minimum_reached, mute_notification_emails, target_groups, skip_collision_check, date_of_last_registration_digest, registrations, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelLodging, lodgings, foods, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelPayment, price_on_request, price_regular, price_regular_early, price_regular_board, price_special, price_special_early, price_special_board, payment_methods, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelAccess, hidden, starttime, endtime, owner_feuser, vips',
        ],
        // Multiple event topic
        '1' => [
            'showitem' =>
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.divLabelGeneral, object_type, title, subtitle, image, categories, requirements, dependencies, teaser, description, event_type, credit_points, additional_information, checkboxes, uses_terms_2, notes, attached_files, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.divLabelAttendees, allows_multiple_registrations, target_groups, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.divLabelPayment, price_on_request, price_regular, price_regular_early, price_regular_board, price_special, price_special_early, price_special_board, payment_methods, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_seminars.divLabelAccess, hidden, starttime, endtime',
        ],
        // Multiple event date
        '2' => [
            'showitem' =>
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelGeneral, object_type, title, topic, language, accreditation_number, details_page, cancelled, automatic_confirmation_cancelation, notes, attached_files, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelPlaceTime, begin_date, end_date, time_zone, timeslots, begin_date_registration, deadline_registration, deadline_early_bird, deadline_unregistration, expiry, place, room, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelSpeakers, speakers, partners, tutors, leaders, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelOrganizers, organizers, organizing_partners, event_takes_place_reminder_sent, cancelation_deadline_reminder_sent, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelAttendees, needs_registration, attendees_min, attendees_max, queue_size, offline_attendees, organizers_notified_about_minimum_reached, mute_notification_emails, skip_collision_check, date_of_last_registration_digest, registrations, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelLodging, lodgings, foods, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db:tx_seminars_seminars.divLabelAccess, hidden, vips',
        ],
    ],
];

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8006000) {
    $tca['columns']['begin_date']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['end_date']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['begin_date_registration']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['deadline_registration']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['deadline_early_bird']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['deadline_unregistration']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['expiry']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['starttime']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['endtime']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['date_of_last_registration_digest']['config']['renderType'] = 'inputDateTime';
    $tca['columns']['details_page']['config']['renderType'] = 'inputLink';
    $tca['columns']['categories']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['event_type']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['place']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['lodgings']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['foods']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['speakers']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['partners']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['tutors']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['leaders']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['checkboxes']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['organizers']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['organizing_partners']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
    $tca['columns']['target_groups']['config']['fieldControl'] = [
        'editPopup' => ['disabled' => false],
        'addRecord' => ['disabled' => false],
        'listModule' => ['disabled' => false],
    ];
} else {
    $tca['columns']['description']['defaultExtras'] = 'richtext[]';
    $tca['columns']['additional_information']['defaultExtras'] = 'richtext[]';
    $tca['columns']['details_page']['config']['wizards'] = [
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
    ];
    $tca['columns']['categories']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_categories'
    );
    $tca['columns']['event_type']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_event_types'
    );
    $tca['columns']['place']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_sites'
    );
    $tca['columns']['lodgings']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_lodgings'
    );
    $tca['columns']['foods']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_foods'
    );
    $tca['columns']['speakers']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_speakers'
    );
    $tca['columns']['partners']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_speakers'
    );
    $tca['columns']['tutors']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_speakers'
    );
    $tca['columns']['leaders']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_speakers'
    );
    $tca['columns']['checkboxes']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_checkboxes'
    );
    $tca['columns']['organizers']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_organizers'
    );
    $tca['columns']['organizing_partners']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_organizers'
    );
    $tca['columns']['target_groups']['config']['wizards'] = \OliverKlee\Seminars\BackEnd\TceForms::replaceTables(
        \OliverKlee\Seminars\BackEnd\TceForms::getWizardConfiguration(),
        'tx_seminars_target_groups'
    );
}

return $tca;
