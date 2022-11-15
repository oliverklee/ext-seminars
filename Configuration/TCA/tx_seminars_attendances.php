<?php

defined('TYPO3_MODE') or die();

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate DESC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/Registration.gif',
        'searchFields' => 'title',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,uid,title,user,seminar,registration_queue,price,seats,registered_themselves,total_price,currency,including_tax,attendees_names,additional_persons,datepaid,method_of_payment,account_number,bank_code,bank_name,account_owner,separate_billing_address,company,gender,name,address,zip,city,country,phone,email,been_there,interests,expectations,background_knowledge,accommodation,food,known_from,notes',
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.title',
            'config' => [
                'type' => 'input',
                'readOnly' => 1,
            ],
        ],
        'uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.uid',
            'config' => [
                'type' => 'none',
            ],
        ],
        'seminar' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.seminar',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_seminars_seminars',
                'default' => 0,
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'user' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.user',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'default' => 0,
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'been_there' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.been_there',
            'config' => [
                'type' => 'check',
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'registration_queue' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.registration_queue',
            'config' => [
                'type' => 'check',
            ],
        ],
        'seats' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.seats',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'max' => 3,
                'eval' => 'int',
                'range' => [
                    'upper' => 999,
                    'lower' => 0,
                ],
                'default' => 1,
            ],
        ],
        'registered_themselves' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.registered_themselves',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'price' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.price',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'total_price' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.total_price',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 10,
                'eval' => 'double2',
                'range' => [
                    'upper' => '999999.99',
                    'lower' => '0.00',
                ],
                'default' => '0.00',
            ],
        ],
        'attendees_names' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.attendees_names',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'additional_persons' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.additional_persons',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'fe_users',
                'foreign_field' => 'tx_seminars_registration',
                'foreign_default_sortby' => 'name',
                'maxitems' => 999,
                'appearance' => [
                    'levelLinksPosition' => 'bottom',
                    'expandSingle' => 1,
                ],
            ],
        ],
        'kids' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.kids',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'max' => 3,
                'eval' => 'int',
                'range' => [
                    'upper' => 999,
                    'lower' => 0,
                ],
                'default' => 0,
            ],
        ],
        'foods' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.foods',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_seminars_foods',
                'foreign_table_where' => 'ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_attendances_foods_mm',
            ],
        ],
        'food' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.food',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'lodgings' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.lodgings',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_seminars_lodgings',
                'foreign_table_where' => 'ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_attendances_lodgings_mm',
            ],
        ],
        'accommodation' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.accommodation',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'checkboxes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.checkboxes',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_seminars_checkboxes',
                'foreign_table_where' => 'ORDER BY title',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
                'MM' => 'tx_seminars_attendances_checkboxes_mm',
            ],
        ],
        'interests' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.interests',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'expectations' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.expectations',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'background_knowledge' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.background_knowledge',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'known_from' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.known_from',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'notes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.notes',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'datepaid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.datepaid',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 8,
                'eval' => 'date, int',
                'default' => 0,
            ],
        ],
        'currency' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.currency',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'static_currencies',
                'default' => 0,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'items' => [['', '0']],
            ],
        ],
        'including_tax' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.including_tax',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.including_tax.including',
                        '0',
                    ],
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.including_tax.excluding',
                        '1',
                    ],
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'method_of_payment' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.method_of_payment',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_seminars_payment_methods',
                'foreign_table_where' => 'ORDER BY title',
                'default' => 0,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        // @deprecated will be removed in seminars 5.0
        'account_number' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.account_number',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        // @deprecated will be removed in seminars 5.0
        'account_owner' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.account_owner',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        // @deprecated will be removed in seminars 5.0
        'bank_code' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.bank_code',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        // @deprecated will be removed in seminars 5.0
        'bank_name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.bank_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'separate_billing_address' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.separate_billing_address',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
            ],
        ],
        'company' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.company',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'text',
                'cols' => 20,
                'rows' => 3,
            ],
        ],
        'gender' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.gender',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.gender.I.0',
                        '0',
                    ],
                    [
                        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.gender.I.1',
                        '1',
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.name',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 80,
                'eval' => 'trim',
            ],
        ],
        'address' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.address',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'text',
                'cols' => 20,
                'rows' => 3,
            ],
        ],
        'zip' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.zip',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'max' => 10,
                'eval' => 'trim',
            ],
        ],
        'city' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.city',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 50,
                'eval' => 'trim',
            ],
        ],
        'country' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.country',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 16,
                'max' => 40,
                'eval' => 'trim',
            ],
        ],
        'telephone' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.telephone',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 20,
                'eval' => 'trim',
            ],
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.email',
            'displayCond' => 'FIELD:separate_billing_address:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 80,
                'eval' => 'trim',
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelOverview, title, uid, seminar, user, been_there, hidden, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelBookingInformation, registration_queue, registered_themselves, seats, price, total_price, attendees_names, additional_persons, kids, foods, food, lodgings, accommodation, checkboxes, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelRegistrationComments, interests, expectations, background_knowledge, known_from, notes, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelPaymentInformation, datepaid, currency, including_tax, method_of_payment, account_number, account_owner, bank_code, bank_name, ' .
                '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelBillingAddress, separate_billing_address, company, gender, name, address, zip, city, country, telephone, email',
        ],
    ],
];

return $tca;
