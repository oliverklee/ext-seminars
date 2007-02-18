<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

// unserialize the configuration array
$globalConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']);
$usePageBrowser = (boolean) $globalConfiguration['usePageBrowser'];
$selectTopicsFromAllPages = (boolean) $globalConfiguration['selectTopicsFromAllPages'];
$selectType = $usePageBrowser ? 'group' : 'select';
$selectWhereForTopics = ($selectTopicsFromAllPages) ? '' : ' AND tx_seminars_seminars.pid=###STORAGE_PID###';

$TCA['tx_seminars_seminars'] = Array (
	'ctrl' => $TCA['tx_seminars_seminars']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,title,subtitle,description,accreditation_number,credit_points,begin_date,end_date,deadline_registration,place,room,speakers,price_regular,price_special,payment_methods,organizers,needs_registration,allows_multiple_registrations,attendees_min,attendees_max,cancelled,attendees,enough_attendees,is_full,owner_feuser,vips,notes'
	),
	'columns' => Array (
		'object_type' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.object_type',
			'config' => Array (
			'type' => 'radio',
				'default' => '0',
				'items' => Array (
					Array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.0', '0'),
					Array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.1', '1'),
					Array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.2', '2'),
				)
			)
		),
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'topic' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.topic',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_seminars',
				'foreign_table' => 'tx_seminars_seminars',
				// only allow for topic records and complete event records, but not for date records
				'foreign_table_where' => 'AND (tx_seminars_seminars.object_type=0 '
					.'OR tx_seminars_seminars.object_type=1)'.$selectWhereForTopics
					.' ORDER BY title',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'subtitle' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.subtitle',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'event_type' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.event_type',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_event_types',
				'foreign_table' => 'tx_seminars_event_types',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => Array(
					'' => ''
				)
			)
		),
		'accreditation_number' => Array (
			'exclude' => '1',
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.accreditation_number',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
			)
		),
		'credit_points' => Array (
			'exclude' => '1',
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.credit_points',
			'config' => Array (
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'begin_date' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.begin_date',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'end_date' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.end_date',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'deadline_registration' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.deadline_registration',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'deadline_early_bird' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.deadline_early_bird',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'place' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.place',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_sites',
				'foreign_table' => 'tx_seminars_sites',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_place_mm',
			)
		),
		'room' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.room',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'additional_times_places' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.additional_times_places',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'speakers' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.speakers',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_speakers',
				'foreign_table' => 'tx_seminars_speakers',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_speakers_mm',
			)
		),
		'price_regular' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_regular',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '8',
				'eval' => 'double2',
				'checkbox' => '0.00',
				'range' => Array (
					'upper' => '99999.99',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'price_regular_early' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_regular_early',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '8',
				'eval' => 'double2',
				'checkbox' => '0.00',
				'range' => Array (
					'upper' => '99999.99',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'price_special' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_special',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '8',
				'eval' => 'double2',
				'checkbox' => '0.00',
				'range' => Array (
					'upper' => '99999.99',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'price_special_early' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_special_early',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '8',
				'eval' => 'double2',
				'checkbox' => '0.00',
				'range' => Array (
					'upper' => '99999.99',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'additional_information' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.additional_information',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'checkboxes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.checkboxes',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_checkboxes',
				'foreign_table' => 'tx_seminars_checkboxes',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_checkboxes_mm',
			)
		),
		'payment_methods' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.payment_methods',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'foreign_table' => 'tx_seminars_payment_methods',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 999,
			)
		),
		'organizers' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.organizers',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_organizers',
				'foreign_table' => 'tx_seminars_organizers',
				'size' => 5,
				'minitems' => 1,
				'maxitems' => 999,
			)
		),
		'needs_registration' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.needs_registration',
			'config' => Array (
				'type' => 'check',
				'default' => 1,
			)
		),
		'allows_multiple_registrations' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.allows_multiple_registrations',
			'config' => Array (
				'type' => 'check',
				'default' => 0,
			)
		),
		'attendees_min' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.attendees_min',
			'config' => Array (
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'attendees_max' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.attendees_max',
			'config' => Array (
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'cancelled' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.cancelled',
			'config' => Array (
				'type' => 'check',
			)
		),
		'attendees' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.attendees',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => Array (
				'type' => 'none',
			)
		),
		'enough_attendees' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.enough_attendees',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => Array (
				'type' => 'none',
			)
		),
		'is_full' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.is_full',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => Array (
				'type' => 'none',
			)
		),
		'owner_feuser' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.owner_feuser',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1
			)
		),
		'vips' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.vips',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_feusers_mm',
			)
		),
		'notes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.notes',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		)
	),
	'types' => Array (
		// Single event
		'0' => Array('showitem' => '--div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelGeneral, object_type, hidden;;1;;1-1-1, title;;;;2-2-2, subtitle;;;;3-3-3, description;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], event_type, accreditation_number, credit_points, additional_information;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], checkboxes, needs_registration, allows_multiple_registrations, cancelled, owner_feuser, vips, notes, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelSpeakers, speakers, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelOrganizers, organizers, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelAttendees, attendees_min, attendees_max, attendees, enough_attendees, is_full, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelPlaceTime, begin_date, end_date, deadline_registration, deadline_early_bird, place, room, additional_times_places, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelPayment, price_regular, price_regular_early, price_special, price_special_early, payment_methods'),
		// Multiple event topic
		'1' => Array('showitem' => '--div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelGeneral, object_type, hidden;;1;;1-1-1, title;;;;2-2-2, subtitle;;;;3-3-3, description;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], event_type, credit_points, additional_information;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], checkboxes, notes, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelPayment, price_regular, price_regular_early, price_special, price_special_early, payment_methods'),
		// Multiple event date
		'2' => Array('showitem' => '--div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelGeneral, object_type, hidden;;1;;1-1-1, title;;;;2-2-2, topic, accreditation_number, needs_registration, allows_multiple_registrations, cancelled, vips, notes, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelSpeakers, speakers, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelOrganizers, organizers, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelAttendees, attendees_min, attendees_max, attendees, enough_attendees, is_full, --div--;LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.divLabelPlaceTime, begin_date, end_date, deadline_registration, deadline_early_bird, place, room, additional_times_places')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, endtime')
	)
);


$TCA['tx_seminars_speakers'] = Array (
	'ctrl' => $TCA['tx_seminars_speakers']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,organization,homepage,description,notes,address,phone_work,phone_home,phone_mobile,fax,email'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'organization' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.organization',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'homepage' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.homepage',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim,nospace',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'picture' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.picture',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 256,
				'uploadfolder' => 'uploads/tx_seminars',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'notes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.notes',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'address' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.address',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'phone_work' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.phone_work',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'phone_home' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.phone_home',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'phone_mobile' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.phone_mobile',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'fax' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.fax',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim,nospace',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2, organization;;;;3-3-3, homepage, description;;;richtext[paste|bold|italic|orderedlist|unorderedlist|link]:rte_transform[mode=ts_css], notes, address, phone_work, phone_home, phone_mobile, fax, email')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);



$TCA['tx_seminars_attendances'] = Array (
	'ctrl' => $TCA['tx_seminars_attendances']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,user,seminar,seats,attendees_names,paid,datepaid,method_of_payment,account_number,bank_code,bank_name,account_owner,gender,name,address,zip,city,country,phone,email,been_there,interests,expectations,background_knowledge,accommodation,food,known_from,notes'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.title',
			'config' => Array (
				'type' => 'none',
			)
		),
		'user' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.user',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'seminar' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.seminar',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_seminars',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'price' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.price',
			'displayCond' => '',
			'config' => Array (
				'type' => 'none',
			)
		),
		'total_price' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.total_price',
			'config' => Array (
				'type' => 'none',
			)
		),
		'seats' => Array (
			'exclude' => '1',
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.seats',
			'config' => Array (
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '999',
					'lower' => '0'
				),
				'default' => '1'
			)
		),
		'attendees_names' => Array (
			'exclude' => '1',
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.attendees_names',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'paid' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.paid',
			'config' => Array (
				'type' => 'check',
			)
		),
		'datepaid' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.datepaid',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'method_of_payment' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.method_of_payment',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'foreign_table' => 'tx_seminars_payment_methods',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => Array(
					'' => ''
				)
			)
		),
		'account_number' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.account_number',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'bank_code' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.bank_code',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'bank_name' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.bank_name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'account_owner' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.account_owner',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'gender' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.gender',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.gender.I.0', '0'),
					Array('LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.gender.I.1', '1'),
				),
				'size' => 1,
				'maxitems' => 1
			)
		),
		'name' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.name',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '80',
				'eval' => 'trim'
			)
		),
		'address' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.address',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3',
			)
		),
		'zip' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.zip',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '10',
				'eval' => 'trim'
			)
		),
		'city' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.city',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '50',
				'eval' => 'trim'
			)
		),
		'country' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.country',
			'config' => Array (
				'type' => 'input',
				'size' => '16',
				'max' => '40',
				'eval' => 'trim'
			)
		),
		'telephone' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.phone',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '20',
				'eval' => 'trim'
			)
		),
		'email' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '80',
				'eval' => 'trim'
			)
		),
		'been_there' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.been_there',
			'config' => Array (
				'type' => 'check',
			)
		),
		'checkboxes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.checkboxes',
			'config' => Array (
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_checkboxes',
				'foreign_table' => 'tx_seminars_checkboxes',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_attendances_checkboxes_mm',
			)
		),
		'interests' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.interests',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'expectations' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.expectations',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'background_knowledge' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.background_knowledge',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'accommodation' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.accommodation',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'food' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.food',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'known_from' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.known_from',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'notes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.notes',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'user;;;;1-1-1, seminar, price, total_price, seats, attendees_names, paid, datepaid, method_of_payment;;2, name;;3, been_there, checkboxes, interests, expectations, background_knowledge, accommodation, food, known_from, notes')
	),
	'palettes' => Array (
		'1' => Array('showitem' => ''),
		'2' => Array('showitem' => 'account_number, bank_code, bank_name, account_owner'),
		'3' => Array('showitem' => 'gender, address, zip, city, country, telephone, email')
	)
);



$TCA['tx_seminars_sites'] = Array (
	'ctrl' => $TCA['tx_seminars_sites']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,address,homepage,directions,notes'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'address' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.address',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'homepage' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.homepage',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim,nospace',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'directions' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.directions',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'notes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.notes',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2, address;;;;3-3-3, homepage, directions;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], notes')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);



$TCA['tx_seminars_organizers'] = Array (
	'ctrl' => $TCA['tx_seminars_organizers']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,homepage,email,email_footer'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'homepage' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.homepage',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim,nospace',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim,nospace',
			)
		),
		'email_footer' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.email_footer',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2, homepage;;;;3-3-3, email, email_footer')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

$TCA['tx_seminars_payment_methods'] = Array (
	'ctrl' => $TCA['tx_seminars_payment_methods']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title, description'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_payment_methods.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_payment_methods.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '10',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2, description')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

$TCA['tx_seminars_event_types'] = Array (
	'ctrl' => $TCA['tx_seminars_event_types']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_event_types.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

$TCA['tx_seminars_checkboxes'] = Array (
	'ctrl' => $TCA['tx_seminars_checkboxes']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_checkboxes.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);


?>
