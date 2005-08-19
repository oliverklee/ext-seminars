<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE=='BE') {
	t3lib_extMgm::addModule('web','txseminarsM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}

t3lib_div::loadTCA('fe_users');
t3lib_div::loadTCA('tt_content');

$tempColumns = Array (
	'tx_seminars_phone_mobile' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.php:fe_users.tx_seminars_phone_mobile',
		'config' => Array (
			'type' => 'input',
			'size' => '30',
		)
	),
	'tx_seminars_matriculation_number' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.php:fe_users.tx_seminars_matriculation_number',
		'config' => Array (
			'type' => 'input',
			'size' => '10',
			'max' => '10',
			'eval' => 'int',
			'checkbox' => '0',
			'range' => Array (
				'upper' => '999999999',
				'lower' => '1'
			),
			'default' => 0
		)
	),
	'tx_seminars_planned_degree' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.php:fe_users.tx_seminars_planned_degree',
		'config' => Array (
			'type' => 'input',
			'size' => '30',
		)
	),
	'tx_seminars_semester' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.php:fe_users.tx_seminars_semester',
		'config' => Array (
			'type' => 'input',
			'size' => '3',
			'max' => '3',
			'eval' => 'int',
			'checkbox' => '0',
			'range' => Array (
				'upper' => '99',
				'lower' => '0'
			),
			'default' => 0
		)
	),
	'tx_seminars_subject' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.php:fe_users.tx_seminars_subject',
		'config' => Array (
			'type' => 'input',
			'size' => '30',
		)
	),
);



$TCA['tx_seminars_seminars'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_seminars',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY begin_date',
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_seminars_seminars.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, title, subtitle, description, begin_date, end_date, place, room, speakers, price_regular, price_special, organizers, needs_registration, attendees_min, attendees_max, cancelled, attendees, enough_attendees, is_full, notes',
	)
);

$TCA['tx_seminars_speakers'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_speakers',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_seminars_speakers.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title, organization, homepage, description, picture, notes, address, phone_work, phone_home, phone_mobile, fax, email',
	)
);

$TCA['tx_seminars_attendances'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_attendances',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_seminars_attendances.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title, user, seminar, paid, datepaid, method_of_payment, been_there, interests, expectations, background_knowledge, known_from, notes',
	)
);

$TCA['tx_seminars_sites'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_sites',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_seminars_sites.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title, address, homepage, directions, notes',
	)
);

$TCA['tx_seminars_organizers'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_organizers',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_seminars_organizers.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title, homepage, email',
	)
);

$TCA['tx_seminars_payment_methods'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:seminars/locallang_db.php:tx_seminars_payment_methods',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_seminars_payment_methods.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title',
	)
);

t3lib_extMgm::addToInsertRecords('tx_seminars_seminars');
t3lib_extMgm::addToInsertRecords('tx_seminars_speakers');

t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('fe_users','tx_seminars_phone_mobile;;;;1-1-1, tx_seminars_matriculation_number, tx_seminars_planned_degree, tx_seminars_semester, tx_seminars_subject');

t3lib_extMgm::allowTableOnStandardPages('tx_seminars_attendances');
t3lib_extMgm::allowTableOnStandardPages('tx_seminars_organizers');
t3lib_extMgm::allowTableOnStandardPages('tx_seminars_payment_methods');
t3lib_extMgm::allowTableOnStandardPages('tx_seminars_seminars');
t3lib_extMgm::allowTableOnStandardPages('tx_seminars_sites');
t3lib_extMgm::allowTableOnStandardPages('tx_seminars_speakers');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:seminars/flexform_pi1_ds.xml');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','Seminars');

t3lib_extMgm::addPlugin(Array('LLL:EXT:seminars/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
?>