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

$TCA['tx_seminars_seminars'] = array(
	'ctrl' => $TCA['tx_seminars_seminars']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,starttime,endtime,title,subtitle,description,accreditation_number,credit_points,begin_date,end_date,deadline_registration,place,room,speakers,price_regular,price_special,payment_methods,organizers,needs_registration,attendees_min,attendees_max,cancelled,attendees,enough_attendees,is_full,vips,notes'
	),
	'columns' => array(
		'object_type' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.object_type',
			'config' => array(
			'type' => 'radio',
				'default' => '0',
				'items' => array(
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.0', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.1', '1'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.2', '2'),
				)
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'topic' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.topic',
			'config' => array(
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
		'subtitle' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.subtitle',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.description',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'event_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.event_type',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_event_types',
				'foreign_table' => 'tx_seminars_event_types',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => ''
				)
			)
		),
		'accreditation_number' => array(
			'exclude' => '1',
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.accreditation_number',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
			)
		),
		'credit_points' => array(
			'exclude' => '1',
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.credit_points',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'begin_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.begin_date',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'end_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.end_date',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'deadline_registration' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.deadline_registration',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'deadline_early_bird' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.deadline_early_bird',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'place' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.place',
			'config' => array(
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
		'room' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.room',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'speakers' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.speakers',
			'config' => array(
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
		'price_regular' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_regular',
			'config' => array(
				'type' => 'input',
				'size' => '5',
				'max' => '5',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '99999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'price_regular_early' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_regular_early',
			'config' => array(
				'type' => 'input',
				'size' => '5',
				'max' => '5',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '99999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'price_special' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_special',
			'config' => array(
				'type' => 'input',
				'size' => '5',
				'max' => '5',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '99999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'price_special_early' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_special_early',
			'config' => array(
				'type' => 'input',
				'size' => '5',
				'max' => '5',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '99999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'payment_methods' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.payment_methods',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'foreign_table' => 'tx_seminars_payment_methods',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 999,
			)
		),
		'organizers' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.organizers',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_organizers',
				'foreign_table' => 'tx_seminars_organizers',
				'size' => 5,
				'minitems' => 1,
				'maxitems' => 999,
			)
		),
		'needs_registration' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.needs_registration',
			'config' => array(
				'type' => 'check',
				'default' => 1,
			)
		),
		'attendees_min' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.attendees_min',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'attendees_max' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.attendees_max',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'cancelled' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.cancelled',
			'config' => array(
				'type' => 'check',
			)
		),
		'attendees' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.attendees',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'none',
			)
		),
		'enough_attendees' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.enough_attendees',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'none',
			)
		),
		'is_full' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.is_full',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'none',
			)
		),
		'vips' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.vips',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_feusers_mm',
			)
		),
		'notes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		)
	),
	'types' => array(
		'0' => array('showitem' => 'object_type, hidden;;1;;1-1-1, title;;;;2-2-2, subtitle;;;;3-3-3, description;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], event_type, accreditation_number, credit_points, begin_date, end_date, deadline_registration, deadline_early_bird, place, room, speakers, price_regular, price_regular_early, price_special, price_special_early, payment_methods, organizers, needs_registration, attendees_min, attendees_max, cancelled, attendees, enough_attendees, is_full, vips, notes'),
		'1' => array('showitem' => 'object_type, hidden;;1;;1-1-1, title;;;;2-2-2, subtitle;;;;3-3-3, description;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], event_type, credit_points, price_regular, price_regular_early, price_special, price_special_early, payment_methods, needs_registration, notes'),
		'2' => array('showitem' => 'object_type, hidden;;1;;1-1-1, title;;;;2-2-2, topic, accreditation_number, begin_date, end_date, deadline_registration, deadline_early_bird, place, room, speakers, organizers, attendees_min, attendees_max, cancelled, attendees, enough_attendees, is_full, vips, notes')
	),
	'palettes' => array(
		'1' => array('showitem' => 'starttime, endtime')
	)
);



$TCA['tx_seminars_speakers'] = array(
	'ctrl' => $TCA['tx_seminars_speakers']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,organization,homepage,description,notes,address,phone_work,phone_home,phone_mobile,fax,email'
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'organization' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.organization',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'homepage' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.homepage',
			'config' => array(
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					'link' => array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.description',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'picture' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.picture',
			'config' => array(
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
		'notes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.address',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'phone_work' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.phone_work',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'phone_home' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.phone_home',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'phone_mobile' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.phone_mobile',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'fax' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.fax',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim,nospace',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, organization;;;;3-3-3, homepage, description;;;richtext[paste|bold|italic|orderedlist|unorderedlist|link]:rte_transform[mode=ts_css], notes, address, phone_work, phone_home, phone_mobile, fax, email')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);



$TCA['tx_seminars_attendances'] = array(
	'ctrl' => $TCA['tx_seminars_attendances']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,user,seminar,seats,paid,datepaid,method_of_payment,been_there,interests,expectations,background_knowledge,accommodation,food,known_from,notes'
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.title',
			'config' => array(
				'type' => 'none',
			)
		),
		'user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.user',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'seminar' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.seminar',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_seminars',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'price' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.price',
			'displayCond' => '',
			'config' => array(
				'type' => 'none',
			)
		),
		'seats' => array(
			'exclude' => '1',
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.seats',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0'
				),
				'default' => '1'
			)
		),
		'paid' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.paid',
			'config' => array(
				'type' => 'check',
			)
		),
		'datepaid' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.datepaid',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'method_of_payment' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.method_of_payment',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'foreign_table' => 'tx_seminars_payment_methods',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => ''
				)
			)
		),
		'been_there' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.been_there',
			'config' => array(
				'type' => 'check',
			)
		),
		'interests' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.interests',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'expectations' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.expectations',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'background_knowledge' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.background_knowledge',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'accommodation' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.accommodation',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'food' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.food',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'known_from' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.known_from',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'notes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'user;;;;1-1-1, seminar, price, seats, paid, datepaid, method_of_payment, been_there, interests, expectations, background_knowledge, accommodation, food, known_from, notes')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);



$TCA['tx_seminars_sites'] = array(
	'ctrl' => $TCA['tx_seminars_sites']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,address,homepage,directions,notes'
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.address',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'homepage' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.homepage',
			'config' => array(
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					'link' => array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'directions' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.directions',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'notes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, address;;;;3-3-3, homepage, directions;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], notes')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);



$TCA['tx_seminars_organizers'] = array(
	'ctrl' => $TCA['tx_seminars_organizers']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,homepage,email,email_footer'
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'homepage' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.homepage',
			'config' => array(
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					'link' => array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim,nospace',
			)
		),
		'email_footer' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers.email_footer',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, homepage;;;;3-3-3, email, email_footer')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);

$TCA['tx_seminars_payment_methods'] = array(
	'ctrl' => $TCA['tx_seminars_payment_methods']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title, description'
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_payment_methods.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_payment_methods.description',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '10',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, description')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);

$TCA['tx_seminars_event_types'] = array(
	'ctrl' => $TCA['tx_seminars_event_types']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title'
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_event_types.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);

?>
