<?php
defined('TYPO3_MODE') or die('Access denied.');

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_seminars=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_speakers=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_attendances=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_sites=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_organizers=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_payment_methods=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_event_types=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_checkboxes=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_lodgings=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_foods=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_target_groups=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_categories=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_seminars_skills=1
');


// Adds our custom function to a hook in t3lib/class.t3lib_tcemain.php
// Used for post-validation of fields in back-end forms.
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:seminars/class.tx_seminars_tcemain.php:tx_seminars_tcemainprocdm';

t3lib_extMgm::addPItoST43(
	$_EXTKEY, 'FrontEnd/DefaultController.php', '_pi1', 'list_type', 0
);
t3lib_extMgm::addTypoScript($_EXTKEY, 'setup', '
	plugin.' . t3lib_extMgm::getCN($_EXTKEY) . '_pi1.userFunc = tx_seminars_FrontEnd_DefaultController->main
', 43);

if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 6002000) {
	t3lib_extMgm::addTypoScript(
		$_EXTKEY,
		'setup', '
		tt_content.shortcut.20.conf.tx_seminars_seminars = < plugin.' . t3lib_extMgm::getCN($_EXTKEY) . '_pi1
		tt_content.shortcut.20.conf.tx_seminars_seminars.CMD = singleView
	',
		43
	);
} else {
	t3lib_extMgm::addTypoScript(
		$_EXTKEY,
		'setup', '
		tt_content.shortcut.20.0.conf.tx_seminars_seminars = < plugin.' . t3lib_extMgm::getCN($_EXTKEY) . '_pi1
		tt_content.shortcut.20.0.conf.tx_seminars_seminars.CMD = singleView
	',
		43
	);
}

// registers the seminars command line interface
$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['seminars'] = array(
	'EXT:seminars/cli/tx_seminars_cli.php', '_CLI_seminars',
);