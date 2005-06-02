<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
# 
# Auto generated 31-05-2005 10:58
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'Seminar Manager',
	'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures and other events as an overview or as detailed descriptions. In addition, it will allow frontend users to register for seminars.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,frontendformslib,newloginbox,salutationswitcher,sr_feuser_register',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'TYPO3_version' => '-',
	'PHP_version' => '-',
	'module' => 'mod1',
	'state' => 'alpha',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'AStA Bonn',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'private' => 0,
	'download_password' => '',
	'version' => '0.3.1',	// Don't modify this! Managed automatically during upload to repository.
	'_md5_values_when_last_written' => 'a:31:{s:29:"class.tx_seminars_seminar.php";s:4:"a477";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"49ae";s:14:"ext_tables.php";s:4:"dd41";s:14:"ext_tables.sql";s:4:"3f46";s:24:"ext_typoscript_setup.txt";s:4:"454a";s:32:"icon_tx_seminars_attendances.gif";s:4:"475a";s:31:"icon_tx_seminars_organizers.gif";s:4:"475a";s:36:"icon_tx_seminars_payment_methods.gif";s:4:"475a";s:29:"icon_tx_seminars_seminars.gif";s:4:"475a";s:26:"icon_tx_seminars_sites.gif";s:4:"475a";s:29:"icon_tx_seminars_speakers.gif";s:4:"475a";s:16:"locallang_db.php";s:4:"3644";s:7:"tca.php";s:4:"df58";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"579b";s:14:"mod1/index.php";s:4:"9ebb";s:18:"mod1/locallang.php";s:4:"ef46";s:22:"mod1/locallang_mod.php";s:4:"cf13";s:19:"mod1/moduleicon.gif";s:4:"8074";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"4f88";s:17:"pi1/locallang.php";s:4:"1b67";s:21:"pi1/seminars_pi1.tmpl";s:4:"61bc";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"1191";s:17:"pi2/locallang.php";s:4:"1dc1";s:21:"pi2/seminars_pi2.tmpl";s:4:"afc0";s:29:"pi3/class.tx_seminars_pi3.php";s:4:"d7e1";s:17:"pi3/locallang.php";s:4:"b0f4";s:21:"pi3/seminars_pi3.tmpl";s:4:"af58";s:14:"doc/manual.sxw";s:4:"06b3";s:40:"static/tx_srfeuserregister_pi1_tmpl.diff";s:4:"18ad";}',
);

?>