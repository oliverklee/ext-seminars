<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 19-11-2006 01:24
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Seminar Manager',
	'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events as an overview or as detailed descriptions. In addition, it allows front end users to register for these events.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,css_styled_content,ameos_formidable,formidabledatetime,formidable_va_preg,rc_formidable_rdt_rte',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1,mod2',
	'state' => 'alpha',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.4.7',
	'_md5_values_when_last_written' => 'a:51:{s:13:"changelog.txt";s:4:"2766";s:25:"class.tx_seminars_bag.php";s:4:"f129";s:33:"class.tx_seminars_configcheck.php";s:4:"7610";s:30:"class.tx_seminars_dbplugin.php";s:4:"029c";s:34:"class.tx_seminars_objectfromdb.php";s:4:"ad6f";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"44e1";s:34:"class.tx_seminars_registration.php";s:4:"6e38";s:37:"class.tx_seminars_registrationbag.php";s:4:"6e0e";s:41:"class.tx_seminars_registrationmanager.php";s:4:"b8da";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"6b19";s:29:"class.tx_seminars_seminar.php";s:4:"9e66";s:32:"class.tx_seminars_seminarbag.php";s:4:"c918";s:29:"class.tx_seminars_tcemain.php";s:4:"990d";s:36:"class.tx_seminars_templatehelper.php";s:4:"5a93";s:21:"ext_conf_template.txt";s:4:"319e";s:12:"ext_icon.gif";s:4:"e79e";s:17:"ext_localconf.php";s:4:"04fa";s:14:"ext_tables.php";s:4:"21b0";s:14:"ext_tables.sql";s:4:"2993";s:19:"flexform_pi1_ds.xml";s:4:"cebc";s:32:"icon_tx_seminars_attendances.gif";s:4:"475a";s:32:"icon_tx_seminars_event_types.gif";s:4:"475a";s:31:"icon_tx_seminars_organizers.gif";s:4:"475a";s:36:"icon_tx_seminars_payment_methods.gif";s:4:"475a";s:29:"icon_tx_seminars_seminars.gif";s:4:"475a";s:26:"icon_tx_seminars_sites.gif";s:4:"475a";s:29:"icon_tx_seminars_speakers.gif";s:4:"475a";s:13:"locallang.xml";s:4:"5445";s:16:"locallang_db.xml";s:4:"4a4a";s:13:"seminars.tmpl";s:4:"c78e";s:7:"tca.php";s:4:"ae12";s:8:"todo.txt";s:4:"f647";s:14:"doc/manual.sxw";s:4:"d092";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1137";s:14:"mod1/index.php";s:4:"e6d2";s:18:"mod1/locallang.xml";s:4:"e964";s:22:"mod1/locallang_mod.xml";s:4:"4b4f";s:19:"mod1/moduleicon.gif";s:4:"8074";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"32d2";s:14:"mod2/index.php";s:4:"c911";s:18:"mod2/locallang.xml";s:4:"c343";s:22:"mod2/locallang_mod.xml";s:4:"7925";s:19:"mod2/moduleicon.gif";s:4:"4f14";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"52d0";s:17:"pi1/locallang.xml";s:4:"b99c";s:20:"pi1/seminars_pi1.css";s:4:"f62a";s:21:"pi1/seminars_pi1.tmpl";s:4:"9236";s:16:"static/setup.txt";s:4:"77bf";s:40:"static/tx_srfeuserregister_pi1_tmpl.tmpl";s:4:"6310";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'css_styled_content' => '',
			'ameos_formidable' => '0.5.0-0.5.0',
			'formidabledatetime' => '0.2.0-0.2.0',
			'formidable_va_preg' => '',
			'rc_formidable_rdt_rte' => '',
			'php' => '4.0.0-',
			'typo3' => '3.8.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'erotea_date2cal' => '',
			'newloginbox' => '',
		),
	),
	'suggests' => array(
	),
);

?>