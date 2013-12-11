<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (!function_exists('txSeminarsReplaceTables')) {
	/**
	 * Replaces the tables markers for the add and list wizard with the given
	 * table name. It's mainly used to simplify the maintaining of the wizard
	 * code (equals in more than 90%) and to get some flexibility.
	 *
	 * @param array $array wizards array with the table markers
	 * @param string $table name of the real database table (e.g. "tx_seminars_seminars")
	 *
	 * @return array wizards array with replaced table markers
	 */
	function txSeminarsReplaceTables(array $array, $table) {
		$array['add']['params']['table'] =
			str_replace('###TABLE###', $table, $array['add']['params']['table']);
		$array['list']['params']['table'] =
			str_replace('###TABLE###', $table, $array['list']['params']['table']);

		return $array;
	}
}

if (!function_exists('txSeminarsGetTableRelationsClause')) {
	/**
	 * Returns the WHERE clause part to limit the entries to the records stored
	 * with the general record storage PID.
	 *
	 * @param string $tableName table name as prefix for the PID column, must not be empty
	 *
	 * @return string WHERE clause for the foreignTable WHERE part, will be
	 *                empty if the storage PID should not be used to filter the
	 *                select options
	 */
	function txSeminarsGetTableRelationsClause($tableName) {
		if (!tx_oelib_configurationProxy::getInstance('seminars')
			->getAsBoolean('useStoragePid')
		) {
			return '';
		}

		return 'AND (' . $tableName . '.pid = ###STORAGE_PID### ' .
			'OR ###STORAGE_PID### = 0)';
	}
}

// unserializes the configuration array
$globalConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']);
$usePageBrowser = (boolean) $globalConfiguration['usePageBrowser'];
$useGeneralRecordStoragePage = (boolean) $globalConfiguration['useStoragePid'];
$selectTopicsFromAllPages = (boolean) $globalConfiguration['selectTopicsFromAllPages'];
$selectType = $usePageBrowser ? 'group' : 'select';
$selectWhereForTopics = ($selectTopicsFromAllPages) ? '' : ' AND tx_seminars_seminars.pid=###STORAGE_PID###';

$tempWizard = array (
	'_PADDING' => 5,
	'_VERTICAL' => 5,
	'edit' => array (
		'type' => 'popup',
		'title' => 'Edit entry',
		'script' => 'wizard_edit.php',
		'popup_onlyOpenIfSelected' => 1,
		'icon' => 'edit2.gif',
		'JSopenParams' => 'height=480,width=640,status=0,menubar=0,scrollbars=1',
	),
	'add' => array (
		'type' => 'script',
		'title' => 'Create new entry',
		'icon' => 'add.gif',
		'params' => array (
			'table'=>'###TABLE###',
			'pid' => ($useGeneralRecordStoragePage ?
				'###STORAGE_PID###' : '###CURRENT_PID###'),
			'setValue' => 'prepend',
		),
		'script' => 'wizard_add.php',
	),
);

if ($selectType == 'select') {
	$tempWizard['list'] = array (
		'type' => 'popup',
		'title' => 'List entries',
		'icon' => 'list.gif',
		'params' => array (
			'table'=>'###TABLE###',
			'pid' => ($useGeneralRecordStoragePage ?
				'###STORAGE_PID###' : '###CURRENT_PID###'),
		),
		'script' => 'wizard_list.php',
		'JSopenParams' => 'height=480,width=640,status=0,menubar=0,scrollbars=1',
	);
}

$TCA['tx_seminars_test'] = array(
	'ctrl' => $TCA['tx_seminars_test']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,starttime,endtime,title'
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'none',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0',
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'none',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y')),
				),
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_test.title',
			'config' => array(
				'type' => 'none',
				'size' => '30',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2'),
	),
	'palettes' => array(
		'1' => array('showitem' => 'starttime, endtime'),
	),
);


$TCA['tx_seminars_seminars'] = array(
	'ctrl' => $TCA['tx_seminars_seminars']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,subtitle,categories,teaser,description,accreditation_number,credit_points,begin_date,end_date,timeslots,begin_date_registration,deadline_registration,deadline_unregistration,expiry,details_page,place,room,speakers,prices,price_regular,price_special,payment_methods,organizers,organizing_partners,event_takes_place_reminder_sent,cancelation_deadline_reminder_sent,needs_registration,allows_multiple_registrations,attendees_min,attendees_max,queue_size,offline_attendees,target_groups,skip_collision_check,registrations,cancelled,notes,attached_files,hidden,starttime,endtime,owner_feuser,vips'
	),
	'columns' => array(
		'object_type' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type',
			'config' => array(
			'type' => 'radio',
				'default' => '0',
				'items' => array(
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.0', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.1', '1'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.object_type.I.2', '2'),
				),
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'topic' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.topic',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_seminars',
				'foreign_table' => 'tx_seminars_seminars',
				// only allow for topic records and complete event records, but not for date records
				'foreign_table_where' => 'AND (tx_seminars_seminars.object_type = 0 OR tx_seminars_seminars.object_type = 1)' .
					$selectWhereForTopics . ' ORDER BY title',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			),
		),
		'subtitle' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.subtitle',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'categories' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.categories',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_categories',
				'foreign_table' => 'tx_seminars_categories',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_categories') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_categories_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_categories'),
			),
		),
		'requirements' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.requirements',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_seminars',
				'foreign_table' => 'tx_seminars_seminars',
				'foreign_table_where' => 'AND tx_seminars_seminars.uid != ###THIS_UID### AND object_type = 1 ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_requirements_mm',
			),
		),
		'dependencies' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.dependencies',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'foreign_table' => 'tx_seminars_seminars',
				'foreign_table_where' => 'AND tx_seminars_seminars.uid != ###THIS_UID### AND object_type = 1 ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_requirements_mm',
				'MM_opposite_field' => 'requirements',
			),
		),
		'teaser' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.teaser',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.description',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'event_type' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.event_type',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_event_types',
				'foreign_table' => 'tx_seminars_event_types',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_event_types') . ' ORDER BY title',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => '',
				),
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_event_types'),
			),
		),
		'accreditation_number' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.accreditation_number',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
			),
		),
		'credit_points' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.credit_points',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0',
				),
				'default' => 0,
			),
		),
		'begin_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.begin_date',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'end_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.end_date',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'timeslots' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.timeslots',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_seminars_timeslots',
				'foreign_field' => 'seminar',
				'foreign_default_sortby' => 'tx_seminars_timeslots.begin_date',
				'maxitems' => 999,
				'appearance' => array(
					'levelLinksPosition' => 'bottom',
					'expandSingle' => 1,
				),
			),
		),
		'begin_date_registration' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.begin_date_registration',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'deadline_registration' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.deadline_registration',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'deadline_early_bird' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.deadline_early_bird',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'deadline_unregistration' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.deadline_unregistration',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'expiry' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.expiry',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'details_page' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.details_page',
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
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
		'place' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.place',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_sites',
				'foreign_table' => 'tx_seminars_sites',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_sites') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_place_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_sites'),
			),
		),
		'room' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.room',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'lodgings' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.lodgings',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_lodgings',
				'foreign_table' => 'tx_seminars_lodgings',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_lodgings') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_lodgings_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_lodgings'),
			),
		),
		'foods' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.foods',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_foods',
				'foreign_table' => 'tx_seminars_foods',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_foods') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_foods_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_foods'),
			),
		),
		'speakers' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.speakers',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_speakers',
				'foreign_table' => 'tx_seminars_speakers',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_speakers')  . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_speakers_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_speakers'),
			),
		),
		'partners' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.partners',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_speakers',
				'foreign_table' => 'tx_seminars_speakers',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_speakers') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_speakers_mm_partners',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_speakers'),
			),
		),
		'tutors' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.tutors',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_speakers',
				'foreign_table' => 'tx_seminars_speakers',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_speakers') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_speakers_mm_tutors',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_speakers'),
			),
		),
		'leaders' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.leaders',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_speakers',
				'foreign_table' => 'tx_seminars_speakers',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_speakers') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_speakers_mm_leaders',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_speakers'),
			),
		),
		'language' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.language',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', ''),
				),
				'itemsProcFunc' => 'tx_staticinfotables_div->selectItemsTCA',
				'itemsProcFunc_config' => array(
					'table' => 'static_languages',
					'where' => '',
					'indexField' => 'lg_iso_2',
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'prices' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.prices',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_seminars_prices',
				'foreign_field' => 'seminar',
				'maxitems' => 999,
			),
		),
		'price_regular' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.price_regular',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'price_regular_early' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.price_regular_early',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'price_regular_board' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.price_regular_board',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'price_special' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.price_special',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'price_special_early' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.price_special_early',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'price_special_board' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.price_special_board',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'additional_information' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.additional_information',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'checkboxes' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.checkboxes',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_checkboxes',
				'foreign_table' => 'tx_seminars_checkboxes',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_checkboxes') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_checkboxes_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_checkboxes'),
			),
		),
		'uses_terms_2' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.uses_terms_2',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'check',
				'default' => 0,
			)
		),
		'payment_methods' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.payment_methods',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'foreign_table' => 'tx_seminars_payment_methods',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_payment_methods') . ' ORDER BY title',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_payment_methods_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_payment_methods'),
			),
		),
		'organizers' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.organizers',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_organizers',
				'foreign_table' => 'tx_seminars_organizers',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_organizers') . ' ORDER BY title',
				'size' => 5,
				'minitems' => 1,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_organizers_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_organizers'),
			),
		),
		'organizing_partners' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.organizing_partners',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_organizers',
				'foreign_table' => 'tx_seminars_organizers',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_organizers') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_organizing_partners_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_organizers'),
			),
		),
		'event_takes_place_reminder_sent' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.event_takes_place_reminder_sent',
			'config' => array(
				'type' => 'check',
				'readOnly' => 1,
			),
		),
		'cancelation_deadline_reminder_sent' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.cancelation_deadline_reminder_sent',
			'config' => array(
				'type' => 'check',
				'readOnly' => 1,
			),
		),
		'needs_registration' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.needs_registration',
			'config' => array(
				'type' => 'check',
				'default' => 1,
			),
		),
		'allows_multiple_registrations' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.allows_multiple_registrations',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'check',
				'default' => 0,
			),
		),
		'attendees_min' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.attendees_min',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '9999',
					'lower' => '0',
				),
				'default' => 0,
			),
		),
		'attendees_max' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.attendees_max',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '9999',
					'lower' => '0',
				),
				'default' => 0,
			),
		),
		'queue_size' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.queue_size',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'check',
				'default' => 0,
			),
		),
		'offline_attendees' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.offline_attendees',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0',
				),
				'default' => 0,
			),
		),
		'target_groups' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.target_groups',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_target_groups',
				'foreign_table' => 'tx_seminars_target_groups',
				'foreign_table_where' => txSeminarsGetTableRelationsClause('tx_seminars_target_groups') . ' ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_target_groups_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_target_groups'),
			),
		),
		'skip_collision_check' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.skip_collision_check',
			'displayCond' => 'FIELD:needs_registration:REQ:true',
			'config' => array(
				'type' => 'check',
				'default' => 0,
			),
		),
		'registrations' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.registrations',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_seminars_attendances',
				'foreign_field' => 'seminar',
				'foreign_default_sortby' => 'tx_seminars_attendances.crdate',
				'maxitems' => 999,
				'appearance' => array(
					'levelLinksPosition' => 'bottom',
					'expandSingle' => 1,
				),
			),
		),
		'cancelled' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.cancelled',
			'config' => array(
				'type' => 'select',
				'default' => '0',
				'items' => array(
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.cancelled_planned', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.cancelled_canceled', '1'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.cancelled_confirmed', '2'),
				),

			),
		),
		'notes' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'attached_files' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.attached_files',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'jpg,jpeg,png,bmp,gif,tiff,tif,' . 'txt,pdf,ps,' .
					'svg,' . 'doc,docx,sxw,odt,' . 'xls,xlsx,sxc,ods,' .
					'ppt,pptx,sxi,odp,' . 'html,htm,css,js,xml,xsd,' .
					'zip,rar,gz,tgz,tar,bz2,tbz,tbz2',
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/tx_seminars/',
				'size' => '5',
				'maxitems' => '200',
				'minitems' => '0',
				'autoSizeMax' => 40,
			),
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0',
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y')),
				),
			),
		),
		'owner_feuser' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:owner_feuser',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'vips' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.vips',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_seminars_feusers_mm',
			),
		),
		'image' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.image',
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
			),
		),
		'publication_hash' => array(
			'exclude' => 1,
			'label' => '',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'max' => '255',
				'eval' => 'trim',
			),
		),
	),
	'types' => array(
		// Single event
		'0' => array('showitem' => '' .
			'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelGeneral, object_type, title;;;;2-2-2, subtitle;;;;3-3-3, image, categories, teaser, description;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], event_type, language, accreditation_number, credit_points, details_page, additional_information;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], checkboxes, uses_terms_2, cancelled, notes, attached_files, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelPlaceTime, begin_date, end_date, timeslots, begin_date_registration, deadline_registration, deadline_early_bird, deadline_unregistration, place, room, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelSpeakers, speakers, partners, tutors, leaders, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelOrganizers, organizers, organizing_partners, event_takes_place_reminder_sent, cancelation_deadline_reminder_sent, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelAttendees, needs_registration, allows_multiple_registrations, attendees_min, attendees_max, queue_size, offline_attendees, target_groups, skip_collision_check, registrations, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelLodging, lodgings, foods, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelPayment, prices, price_regular, price_regular_early, price_regular_board, price_special, price_special_early, price_special_board, payment_methods, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelAccess, hidden;;1;;1-1-1, owner_feuser, vips',
		),
		// Multiple event topic
		'1' => array('showitem' =>
			'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelGeneral, object_type, title;;;;2-2-2, subtitle;;;;3-3-3, image, categories, requirements, dependencies, teaser, description;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], event_type, credit_points, additional_information;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], checkboxes, uses_terms_2, notes, attached_files, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelAttendees, allows_multiple_registrations, target_groups, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelPayment, prices, price_regular, price_regular_early, price_regular_board, price_special, price_special_early, price_special_board, payment_methods, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelAccess, hidden;;1;;1-1-1, ',
		),
		// Multiple event date
		'2' => array('showitem' =>
			'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelGeneral, object_type, title;;;;2-2-2, topic, language, accreditation_number, details_page, cancelled, notes, attached_files, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelPlaceTime, begin_date, end_date, timeslots, begin_date_registration, deadline_registration, deadline_early_bird, deadline_unregistration, expiry, place, room, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelSpeakers, speakers, partners, tutors, leaders, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelOrganizers, organizers, organizing_partners, event_takes_place_reminder_sent, cancelation_deadline_reminder_sent, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelAttendees, needs_registration, attendees_min, attendees_max, queue_size, offline_attendees, skip_collision_check, registrations, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelLodging, lodgings, foods, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.divLabelAccess, hidden;;1;;1-1-1, vips',
		),
	),
	'palettes' => array(
		'1' => array('showitem' => 'starttime, endtime'),
	),
);


$TCA['tx_seminars_speakers'] = array(
	'ctrl' => $TCA['tx_seminars_speakers']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,title,organization,homepage,description,skills,notes,address,phone_work,phone_home,phone_mobile,fax,email,cancelation_period,owner'
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'gender' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.gender',
			'config' => array(
			'type' => 'radio',
				'default' => '0',
				'items' => array(
					array('', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.gender_male', '1'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.gender_female', '2'),
				),
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			),
		),
		'organization' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.organization',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'homepage' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.homepage',
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
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.description',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'skills' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.skills',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_skills',
				'foreign_table' => 'tx_seminars_skills',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_speakers_skills_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_skills'),
			),
		),
		'notes' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'address' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.address',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'phone_work' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.phone_work',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'phone_home' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.phone_home',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'phone_mobile' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.phone_mobile',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'fax' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.fax',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'email' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim,nospace',
			),
		),
		'cancelation_period' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_speakers.cancelation_period',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'num',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0',
				),
			),
		),
		'owner' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:owner_feuser',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden, title, gender;;;;2-2-2, organization;;;;3-3-3, homepage, description;;;richtext[paste|bold|italic|orderedlist|unorderedlist|link]:rte_transform[mode=ts_css],skills, notes, address, phone_work, phone_home, phone_mobile, fax, email, cancelation_period, owner'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	)
);



$TCA['tx_seminars_attendances'] = array(
	'ctrl' => $TCA['tx_seminars_attendances']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,uid,title,user,seminar,registration_queue,price,seats,registered_themselves,total_price,currency,tax,including_tax,attendees_names,additional_persons,datepaid,method_of_payment,account_number,bank_code,bank_name,account_owner,company,gender,name,address,zip,city,country,phone,email,been_there,interests,expectations,background_knowledge,accommodation,food,known_from,notes',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.title',
			'config' => array(
				'type' => 'input',
				'readOnly' => 1,
			),
		),
		'uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.uid',
			'config' => array(
				'type' => 'none',
			),
		),
		'seminar' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.seminar',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_seminars',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			),
		),
		'user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.user',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			),
		),
		'been_there' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.been_there',
			'config' => array(
				'type' => 'check',
			),
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'registration_queue' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.registration_queue',
			'config' => array(
				'type' => 'check',
			),
		),
		'seats' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.seats',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0',
				),
				'default' => '1',
			),
		),
		'registered_themselves' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.registered_themselves',
			'config' => array(
				'type' => 'check',
				'default' => '1',
			),
		),
		'price' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.price',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'total_price' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.total_price',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'attendees_names' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.attendees_names',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'additional_persons' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.additional_persons',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'fe_users',
				'foreign_field' => 'tx_seminars_registration',
				'foreign_default_sortby' => 'name',
				'maxitems' => 999,
				'appearance' => array(
					'levelLinksPosition' => 'bottom',
					'expandSingle' => 1,
				),
			),
		),
		'kids' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.kids',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'upper' => '999',
					'lower' => '0',
				),
				'default' => '0',
			),
		),
		'foods' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.foods',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_foods',
				'foreign_table' => 'tx_seminars_foods',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_attendances_foods_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_foods'),
			),
		),
		'food' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.food',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'lodgings' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.lodgings',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_lodgings',
				'foreign_table' => 'tx_seminars_lodgings',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_attendances_lodgings_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_lodgings'),
			),
		),
		'accommodation' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.accommodation',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'checkboxes' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.checkboxes',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_checkboxes',
				'foreign_table' => 'tx_seminars_checkboxes',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_attendances_checkboxes_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_checkboxes'),
			),
		),
		'interests' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.interests',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'expectations' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.expectations',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'background_knowledge' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.background_knowledge',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'known_from' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.known_from',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'notes' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'datepaid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.datepaid',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'currency' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.currency',
			'config' => array(
				'type' => 'select',
				'internal_type' => 'db',
				'allowed' => 'static_currencies',
				'foreign_table' => 'static_currencies',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => '',
				),
			),
		),
		'including_tax' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.including_tax',
			'config' => array(
			'type' => 'select',
				'default' => '0',
				'items' => array(
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.including_tax.including', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.including_tax.excluding', '1'),
				),
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			),
		),
		'tax' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.tax',
			'config' => array(
				'type' => 'select',
				'internal_type' => 'db',
				'allowed' => 'static_taxes',
				'foreign_table' => 'static_taxes',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => '',
				),
			),
		),
		'method_of_payment' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.method_of_payment',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_payment_methods',
				'foreign_table' => 'tx_seminars_payment_methods',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => '',
				),
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_payment_methods'),
			),
		),
		'account_number' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.account_number',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'account_owner' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.account_owner',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'bank_code' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.bank_code',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'bank_name' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.bank_name',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'company' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.company',
			'config' => array(
				'type' => 'text',
				'cols' => '20',
				'rows' => '3',
			),
		),
		'gender' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.gender',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.gender.I.0', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.gender.I.1', '1')
				),
				'size' => 1,
				'maxitems' => 1,
			),
		),
		'name' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.name',
			'config' => array(
				'type' => 'input',
				'size' => '40',
				'max' => '80',
				'eval' => 'trim',
			),
		),
		'address' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.address',
			'config' => array(
				'type' => 'text',
				'cols' => '20',
				'rows' => '3',
			),
		),
		'zip' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.zip',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '10',
				'eval' => 'trim',
			),
		),
		'city' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.city',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'max' => '50',
				'eval' => 'trim',
			),
		),
		'country' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.country',
			'config' => array(
				'type' => 'input',
				'size' => '16',
				'max' => '40',
				'eval' => 'trim',
			),
		),
		'telephone' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.telephone',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'max' => '20',
				'eval' => 'trim',
			),
		),
		'email' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.email',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'max' => '80',
				'eval' => 'trim',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => '' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.divLabelOverview, title, uid, seminar, user;;;;1-1-1, been_there, hidden;;;;1-1-1, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.divLabelBookingInformation, registration_queue, registered_themselves, seats, price, total_price, attendees_names, additional_persons, kids, foods, food, lodgings, accommodation, checkboxes, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.divLabelRegistrationComments, interests, expectations, background_knowledge, known_from, notes, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.divLabelPaymentInformation, datepaid, currency, including_tax, tax, method_of_payment, account_number, account_owner, bank_code, bank_name, ' .
				'--div--;LLL:EXT:seminars/locallang_db.xml:tx_seminars_attendances.divLabelBillingAddress, company, gender, name, address, zip, city, country, telephone, email'
		),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
);



$TCA['tx_seminars_sites'] = array(
	'ctrl' => $TCA['tx_seminars_sites']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,address,zip,city,homepage,directions,notes,owner',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.address',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'zip' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.zip',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'city' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.city',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'country' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.country',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'itemsProcFunc' => 'tx_staticinfotables_div->selectItemsTCA',
				'itemsProcFunc_config' => array(
					'table' => 'static_countries',
					'where' => '',
					'indexField' => 'cn_iso_2',
				),
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
			),
		),
		'homepage' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.homepage',
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
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
		'directions' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.directions',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'notes' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_sites.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'owner' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:owner_feuser',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, address;;;;3-3-3, zip, city, country, homepage;;;;3-3-3, directions;;;richtext[paste|bold|italic|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts_css], notes, owner'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	)
);



$TCA['tx_seminars_organizers'] = array(
	'ctrl' => $TCA['tx_seminars_organizers']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,description,homepage,email,email_footer'
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_organizers.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_organizers.description',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'homepage' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_organizers.homepage',
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
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
		'email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_organizers.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim,nospace',
			),
		),
		'email_footer' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_organizers.email_footer',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
		'attendances_pid' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_organizers.attendances_pid',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, description;;;richtext[paste|bold|italic|orderedlist|unorderedlist|link]:rte_transform[mode=ts_css], homepage;;;;3-3-3, email, email_footer, attendances_pid'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
);

$TCA['tx_seminars_payment_methods'] = array(
	'ctrl' => $TCA['tx_seminars_payment_methods']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title, description',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_payment_methods.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_payment_methods.description',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '10',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, description'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	)
);

$TCA['tx_seminars_event_types'] = array(
	'ctrl' => $TCA['tx_seminars_event_types']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title, single_view_page',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_event_types.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'single_view_page' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_event_types.single_view_page',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, single_view_page'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	)
);

$TCA['tx_seminars_checkboxes'] = array(
	'ctrl' => $TCA['tx_seminars_checkboxes']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,owner',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_checkboxes.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'owner' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:owner_feuser',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, owner'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	)
);

$TCA['tx_seminars_lodgings'] = array(
	'ctrl' => $TCA['tx_seminars_lodgings']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_lodgings.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
);

$TCA['tx_seminars_foods'] = array(
	'ctrl' => $TCA['tx_seminars_foods']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_foods.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	)
);

$TCA['tx_seminars_timeslots'] = array(
	'ctrl' => $TCA['tx_seminars_timeslots']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'begin_date, end_date, entry_date, speakers, place, room'
	),
	'columns' => array(
		'seminar' => array(
			'config' => array(
				'type' => 'input',
				'size' => '30',
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			),
		),
		'begin_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots.begin_date',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime, required',
				'checkbox' => '0',
				'default' => '0',
			)
		),
		'end_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots.end_date',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'entry_date' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots.entry_date',
			'config' => array(
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
			),
		),
		'speakers' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots.speakers',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_speakers',
				'foreign_table' => 'tx_seminars_speakers',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_timeslots_speakers_mm',
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_speakers'),
			),
		),
		'place' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots.place',
			'config' => array(
				'type' => $selectType,
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_sites',
				'foreign_table' => 'tx_seminars_sites',
				'foreign_table_where' => 'ORDER BY title',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => '',
				),
				'wizards' => txSeminarsReplaceTables($tempWizard, 'tx_seminars_sites'),
			),
		),
		'room' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_timeslots.room',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'begin_date, end_date, entry_date, speakers, place, room'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
);

$TCA['tx_seminars_target_groups'] = array(
	'ctrl' => $TCA['tx_seminars_target_groups']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,minimum_age,maximum_age,owner',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_target_groups.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'minimum_age' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_target_groups.minimum_age',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'lower' => '0',
					'upper' => '199',
		),
			),
		),
		'maximum_age' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_target_groups.maximum_age',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'eval' => 'int',
				'checkbox' => '0',
				'range' => array(
					'lower' => '0',
					'upper' => '199',
				),
			),
		),
		'owner' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:owner_feuser',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, minimum_age, maximum_age, owner'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
);

$TCA['tx_seminars_categories'] = array(
	'ctrl' => $TCA['tx_seminars_categories']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title, icon, single_view_page',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_categories.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'icon' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_categories.icon',
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
			),
		),
		'single_view_page' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_categories.single_view_page',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title, icon;;;;2-2-2, single_view_page'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
);

$TCA['tx_seminars_skills'] = array(
	'ctrl' => $TCA['tx_seminars_skills']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_skills.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
);

$TCA['tx_seminars_prices'] = array(
	'ctrl' => $TCA['tx_seminars_prices']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,value,currency,tax,including_tax,starttime,endtime,fe_group',
	),
	'columns' => array(
		'seminar' => array(
			'config' => array(
				'type' => 'input',
				'size' => '30',
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_seminars.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			),
		),
		'value' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_prices.value',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '10',
				'eval' => 'tx_oelib_Double3Validator',
				'checkbox' => '0.000',
				'range' => array(
					'upper' => '999999.999',
					'lower' => '0.000',
				),
				'default' => '0.000',
			),
		),
		'currency' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_prices.currency',
			'config' => array(
				'type' => 'select',
				'internal_type' => 'db',
				'allowed' => 'static_currencies',
				'foreign_table' => 'static_currencies',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => '',
				),
			),
		),
		'tax' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_prices.tax',
			'config' => array(
				'type' => 'select',
				'internal_type' => 'db',
				'allowed' => 'static_taxes',
				'foreign_table' => 'static_taxes',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'items' => array(
					'' => '',
				),
			),
		),
		'including_tax' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:tx_seminars_prices.including_tax',
			'config' => array(
			'type' => 'select',
				'default' => '0',
				'items' => array(
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_prices.including_tax.including', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:tx_seminars_prices.including_tax.excluding', '1'),
				),
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0',
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y')),
				),
			),
		),
		'fe_group' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--'),
				),
				'foreign_table' => 'fe_groups',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2,value,currency,tax,including_tax,starttime,endtime,fe_group'),
	),
	'palettes' => array(
		'1' => array('showitem' => 'starttime, endtime, fe_group'),
	),
);