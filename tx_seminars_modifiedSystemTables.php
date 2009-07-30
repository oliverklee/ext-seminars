<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

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
	),
	1
);

t3lib_extMgm::addToAllTCAtypes(
	'fe_groups',
	'--div--;LLL:EXT:seminars/locallang_db.xml:fe_groups.tab_event_management,' .
		'tx_seminars_publish_events;;;;1-1-1,tx_seminars_auxiliary_records_pid,'.
		'tx_seminars_reviewer'
);
?>