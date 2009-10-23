<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

if (!function_exists('tx_seminars_tableRelations')) {
	/**
	 * Returns the WHERE clause part to limit the entries to the records stored
	 * with the general record storage PID.
	 *
	 * @param string table name as prefix for the PID column, must not be empty
	 *
	 * @return string WHERE clause for the foreignTable WHERE part, will be
	 *                empty if the storage PID should not be used to filter the
	 *                select options
	 */
	function tx_seminars_tableRelations($tableName) {
		if (!tx_oelib_configurationProxy::getInstance('seminars')
			->getConfigurationValueBoolean('useStoragePid')
		) {
			return '';
		}

		return 'AND (' . $tableName . '.pid = ###STORAGE_PID### ' .
			'OR ###STORAGE_PID### = 0)';
	}
}

t3lib_extMgm::addLLrefForTCAdescr(
	'fe_groups',
	'EXT:seminars/Resources/Private/Language/locallang_csh_fe_groups.xml'
);

t3lib_div::loadTCA('fe_groups');
t3lib_extMgm::addTCAcolumns(
	'fe_groups',
	array(
		'tx_seminars_publish_events' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_publish_events',
			'config' => array(
				'type' => 'radio',
				'default' => '0',
				'items' => array(
					array('LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_publish_events.I.0', '0'),
					array('LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_publish_events.I.1', '1'),
					array('LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_publish_events.I.2', '2'),
				),
			),
		),
		'tx_seminars_events_pid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_events_pid',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'minitems' => '0',
				'maxitems' => '1',
			),
		),
		'tx_seminars_auxiliary_records_pid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_auxiliary_records_pid',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'minitems' => '0',
				'maxitems' => '1',
			),
		),
		'tx_seminars_reviewer' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_reviewer',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'be_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'tx_seminars_default_categories' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:fe_groups.tx_seminars_default_categories',
			'config' => array(
				'type' => 'select',
				'internal_type' => 'db',
				'allowed' => 'tx_seminars_categories',
				'foreign_table' => 'tx_seminars_categories',
				'foreign_table_where' => tx_seminars_tableRelations(
					'tx_seminars_categories'
				),
				'size' => 10,
				'minitems' => 0,
				'maxitems' => 999,
				'MM' => 'tx_seminars_usergroups_categories_mm',
				'wizards' => array(
					'list' => array(
					 	'type' => 'popup',
						'title' => 'List entries',
						'icon' => 'list.gif',
						'params' => array (
							'table'=>'tx_seminars_categories',
							'pid' => ((boolean) $globalConfiguration['useStoragePid'] ?
								'###STORAGE_PID###' : '###CURRENT_PID###'),
						),
						'script' => 'wizard_list.php',
						'JSopenParams' => 'height=480,width=640,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
	),
	1
);

t3lib_extMgm::addToAllTCAtypes(
	'fe_groups',
	'--div--;LLL:EXT:seminars/locallang_db.xml:fe_groups.tab_event_management,' .
		'tx_seminars_publish_events;;;;1-1-1,tx_seminars_events_pid,' .
		'tx_seminars_auxiliary_records_pid,tx_seminars_reviewer,' .
		'tx_seminars_default_categories'
);

t3lib_div::loadTCA('be_groups');
t3lib_extMgm::addTCAcolumns(
	'be_groups',
	array(
		'tx_seminars_events_folder' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:be_groups.tx_seminars_events_folder',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'minitems' => '0',
				'maxitems' => '1',
			),
		),
		'tx_seminars_registrations_folder' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:seminars/locallang_db.xml:be_groups.tx_seminars_registrations_folder',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'minitems' => '0',
				'maxitems' => '1',
			),
		),
	),
	1
);

t3lib_extMgm::addToAllTCAtypes(
	'be_groups',
	'--div--;LLL:EXT:seminars/locallang_db.xml:be_groups.tab_event_management,' .
		'tx_seminars_events_folder,tx_seminars_registrations_folder,'
);
?>