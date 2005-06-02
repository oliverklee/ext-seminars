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

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_seminars_pi1 = < plugin.tx_seminars_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_seminars_pi1.php','_pi1','list_type', 0);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_seminars_seminars = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_seminars_seminars.CMD = singleView
',43);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_seminars_pi2 = < plugin.tx_seminars_pi2.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_seminars_pi2.php','_pi2','list_type', 0);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_seminars_seminars = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi2
	tt_content.shortcut.20.0.conf.tx_seminars_seminars.CMD = singleView
',43);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_seminars_pi3 = < plugin.tx_seminars_pi3.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_seminars_pi3.php','_pi3','list_type', 0);

?>