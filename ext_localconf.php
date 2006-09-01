<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

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
	options.saveDocNew.tx_seminars_event_type=1
');


// Adds our custom function to a hook in t3lib/class.t3lib_tcemain.php
// Used for post-validation of fields in backend forms.
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:seminars/class.tx_seminars_tcemain.php:tx_seminars_tcemainprocdm';

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','Seminar Manager Setup');

// Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_seminars_pi1 = < plugin.tx_seminars_pi1.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_seminars_pi1.php','_pi1','list_type', 0);

t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_seminars_seminars = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_seminars_seminars.CMD = singleView
',43);

?>
