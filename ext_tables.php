<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

// Retrieve the path to the extension's directory.
$extRelPath = t3lib_extMgm::extRelPath($_EXTKEY);
$extPath = t3lib_extMgm::extPath($_EXTKEY);
$extIconRelPath = $extRelPath . 'icons/';

if (TYPO3_MODE=='BE') {
	t3lib_extMgm::addModule('web', 'txseminarsM1', '', $extPath.'mod1/');
	t3lib_extMgm::addModule('web', 'txseminarsM2', '', $extPath.'mod2/');
}

t3lib_div::loadTCA('fe_users');
t3lib_div::loadTCA('tt_content');

$tempColumns = array(
	'tx_seminars_phone_mobile' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_users.tx_seminars_phone_mobile',
		'config' => array(
			'type' => 'input',
			'size' => '30',
			'eval' => 'trim'
		)
	),
	'tx_seminars_matriculation_number' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_users.tx_seminars_matriculation_number',
		'config' => array(
			'type' => 'input',
			'size' => '10',
			'max' => '10',
			'eval' => 'int',
			'checkbox' => '0',
			'range' => array(
				'upper' => '999999999',
				'lower' => '1'
			),
			'default' => 0
		)
	),
	'tx_seminars_planned_degree' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_users.tx_seminars_planned_degree',
		'config' => array(
			'type' => 'input',
			'size' => '30',
			'eval' => 'trim'
		)
	),
	'tx_seminars_semester' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_users.tx_seminars_semester',
		'config' => array(
			'type' => 'input',
			'size' => '3',
			'max' => '3',
			'eval' => 'int',
			'checkbox' => '0',
			'range' => array(
				'upper' => '99',
				'lower' => '0'
			),
			'default' => 0
		)
	),
	'tx_seminars_subject' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_users.tx_seminars_subject',
		'config' => array(
			'type' => 'input',
			'size' => '30',
			'eval' => 'trim'
		)
	)
);

$TCA['tx_seminars_seminars'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars',
		'label' => 'title',
		'type' => 'object_type',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY begin_date',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'iconfile' => $extIconRelPath.'icon_tx_seminars_seminars_complete.gif',
		'typeicon_column' => 'object_type',
		'typeicons' => array(
			'0' => $extIconRelPath.'icon_tx_seminars_seminars_complete.gif',
			'1' => $extIconRelPath.'icon_tx_seminars_seminars_topic.gif',
			'2' => $extIconRelPath.'icon_tx_seminars_seminars_date.gif'
		),
		'dynamicConfigFile' => $extPath.'tca.php',
		'dividers2tabs' => true,
		'hideAtCopy' => true
	)
);

// unserialize the configuration array
$globalConfiguration = unserialize(
	$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]
);
if ($globalConfiguration['useManualSorting']) {
	$TCA['tx_seminars_seminars']['ctrl']['sortby'] = 'sorting';
}

$TCA['tx_seminars_speakers'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_speakers.gif'
	)
);

$TCA['tx_seminars_attendances'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate DESC',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_attendances.gif'
	)
);

$TCA['tx_seminars_sites'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_sites.gif'
	)
);

$TCA['tx_seminars_organizers'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_organizers',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_organizers.gif'
	)
);

$TCA['tx_seminars_payment_methods'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_payment_methods',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_payment_methods.gif'
	)
);

$TCA['tx_seminars_event_types'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_event_types',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_event_types.gif'
	)
);

$TCA['tx_seminars_checkboxes'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_checkboxes',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_checkboxes.gif'
	)
);

$TCA['tx_seminars_lodgings'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_lodgings',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_lodgings.gif'
	)
);

$TCA['tx_seminars_foods'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_foods',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_foods.gif'
	)
);

$TCA['tx_seminars_timeslots'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_timeslots.gif',
		'dynamicConfigFile' => $extPath.'tca.php'
	)
);

$TCA['tx_seminars_target_groups'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_target_groups',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $extPath.'tca.php',
		'iconfile' => $extIconRelPath.'icon_tx_seminars_target_groups.gif'
	)
);

t3lib_extMgm::addToInsertRecords('tx_seminars_seminars');
t3lib_extMgm::addToInsertRecords('tx_seminars_speakers');

t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('fe_users','tx_seminars_phone_mobile;;;;1-1-1, tx_seminars_matriculation_number, tx_seminars_planned_degree, tx_seminars_semester, tx_seminars_subject');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:seminars/flexform_pi1_ds.xml');

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Seminars');

t3lib_extMgm::addPlugin(Array('LLL:EXT:seminars/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'), 'list_type');

?>
