<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

include_once(t3lib_extMgm::extPath($_EXTKEY) . 'class.tx_seminars_flexForms.php');
include_once(t3lib_extMgm::extPath($_EXTKEY) . 'tx_seminars_modifiedSystemTables.php');

t3lib_extMgm::addLLrefForTCAdescr(
	'tx_seminars_seminars',
	'EXT:seminars/Resources/Private/Language/locallang_csh_seminars.xml'
);

// Retrieve the path to the extension's directory.
$extRelPath = t3lib_extMgm::extRelPath($_EXTKEY);
$extPath = t3lib_extMgm::extPath($_EXTKEY);
$extIconRelPath = $extRelPath . 'Resources/Public/Icons/';
$tcaPath = $extPath . 'Configuration/TCA/tca.php';

if (TYPO3_MODE === 'BE') {
	t3lib_extMgm::addModule('web', 'txseminarsM2', '', $extPath . 'BackEnd/');
}

t3lib_div::loadTCA('tt_content');

$TCA['tx_seminars_test'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_test',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY uid',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Test.gif',
		'searchFields' => 'title'
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
		'default_sortby' => 'ORDER BY begin_date DESC',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'iconfile' => $extIconRelPath.'EventComplete.gif',
		'typeicon_column' => 'object_type',
		'typeicons' => array(
			'0' => $extIconRelPath.'EventComplete.gif',
			'1' => $extIconRelPath.'EventTopic.gif',
			'2' => $extIconRelPath.'EventDate.gif'
		),
		'dynamicConfigFile' => $tcaPath,
		'dividers2tabs' => TRUE,
		'hideAtCopy' => TRUE,
		'requestUpdate' => 'needs_registration',
		'searchFields' => 'title,accreditation_number'
	)
);

$TCA['tx_seminars_speakers'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Speaker.gif',
		'searchFields' => 'title'
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
		'enablecolumns' => array(
			'disabled' => 'hidden'
		),
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Registration.gif',
		'dividers2tabs' => TRUE,
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Place.gif',
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Organizer.gif',
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'PaymentMethod.gif',
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'EventType.gif',
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Checkbox.gif',
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Lodging.gif',
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Food.gif',
		'searchFields' => 'title'
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
		'iconfile' => $extIconRelPath.'TimeSlot.gif',
		'dynamicConfigFile' => $tcaPath,
		'searchFields' => 'title'
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
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'TargetGroup.gif',
		'searchFields' => 'title'
	)
);

$TCA['tx_seminars_categories'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_categories',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Category.gif',
		'searchFields' => 'title'
	)
);

$TCA['tx_seminars_skills'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_skills',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Skill.gif',
		'searchFields' => 'title'
	)
);

$TCA['tx_seminars_prices'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_prices',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		),
		'dynamicConfigFile' => $tcaPath,
		'iconfile' => $extIconRelPath . 'Price.gif',
		'searchFields' => 'title'
	)
);

t3lib_extMgm::addToInsertRecords('tx_seminars_seminars');
t3lib_extMgm::addToInsertRecords('tx_seminars_speakers');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

t3lib_extMgm::addPiFlexFormValue(
	$_EXTKEY . '_pi1',
	'FILE:EXT:seminars/Configuration/FlexForms/flexforms_pi1.xml'
);

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Seminars');

t3lib_extMgm::addPlugin(
	array(
		'LLL:EXT:seminars/locallang_db.xml:tt_content.list_type_pi1',
		$_EXTKEY.'_pi1',
		t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif',
	),
	'list_type'
);

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_seminars_FrontEnd_WizardIcon']
		= t3lib_extMgm::extPath($_EXTKEY).'FrontEnd/WizardIcon.php';
}