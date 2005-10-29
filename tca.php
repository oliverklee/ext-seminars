<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_seminars_seminars'] = Array (
	'ctrl' => $TCA['tx_seminars_seminars']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,title,subtitle,description,begin_date,end_date,place,room,speakers,price_regular,payment_methods,organizers,needs_registration,attendees_min,attendees_max,cancelled,attendees,enough_attendees,is_full,notes'
	),
	'columns' => Array (
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
		'place' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.place',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_sites',
				'size' => 3,
				'minitems' => 0,
				'maxitems' => 3,
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
		'speakers' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.speakers',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_speakers',
				'size' => 3,
				'minitems' => 0,
				'maxitems' => 3,
				'MM' => 'tx_seminars_seminars_speakers_mm',
			)
		),
		'price_regular' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.price_regular',
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '9999',
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
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '9999',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'payment_methods' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.payment_methods',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'size' => 3,
				'minitems' => 0,
				'maxitems' => 3,
			)
		),
		'organizers' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.organizers',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_organizers',
				'size' => 3,
				'minitems' => 0,
				'maxitems' => 3,
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
		'attendees_min' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.attendees_min',
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '100',
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
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => Array (
					'upper' => '100',
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
		'notes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars.notes',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, subtitle;;;;3-3-3, description;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], begin_date, end_date, place, room, speakers, price_regular, payment_methods, organizers, needs_registration, attendees_min, attendees_max, cancelled, attendees, enough_attendees, is_full, notes')
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
		'0' => Array('showitem' => 'title;;;;2-2-2, organization;;;;3-3-3, homepage, description;;;richtext[paste|bold|italic|orderedlist|unorderedlist|link]:rte_transform[mode=ts], notes, address, phone_work, phone_home, phone_mobile, fax, email')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);



$TCA['tx_seminars_attendances'] = Array (
	'ctrl' => $TCA['tx_seminars_attendances']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,user,seminar,paid,datepaid,method_of_payment,been_there,interests,expectations,background_knowledge,known_from,notes'
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
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'been_there' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances.been_there',
			'config' => Array (
				'type' => 'check',
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
		'0' => Array('showitem' => 'user;;;;1-1-1, seminar, paid, datepaid, method_of_payment, been_there, interests, expectations, background_knowledge, known_from, notes')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
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
		'0' => Array('showitem' => 'title;;;;2-2-2, address;;;;3-3-3, homepage, directions;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], notes')
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

?>