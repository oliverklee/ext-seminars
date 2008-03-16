<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 09-11-2007 21:12
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Seminar Manager',
	'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,css_styled_content,ameos_formidable,static_info_tables',
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
	'version' => '0.5.99',
	'_md5_values_when_last_written' => 'a:102:{s:13:"changelog.txt";s:4:"b4a3";s:25:"class.tx_seminars_bag.php";s:4:"562b";s:33:"class.tx_seminars_configcheck.php";s:4:"64a6";s:34:"class.tx_seminars_configgetter.php";s:4:"fb3b";s:30:"class.tx_seminars_dbplugin.php";s:4:"6f97";s:34:"class.tx_seminars_objectfromdb.php";s:4:"9f01";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"7ffd";s:31:"class.tx_seminars_organizer.php";s:4:"073c";s:34:"class.tx_seminars_organizerbag.php";s:4:"6924";s:27:"class.tx_seminars_place.php";s:4:"6d89";s:30:"class.tx_seminars_placebag.php";s:4:"ac9a";s:34:"class.tx_seminars_registration.php";s:4:"1e7c";s:37:"class.tx_seminars_registrationbag.php";s:4:"4e33";s:41:"class.tx_seminars_registrationmanager.php";s:4:"a8eb";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"f338";s:29:"class.tx_seminars_seminar.php";s:4:"5c6f";s:32:"class.tx_seminars_seminarbag.php";s:4:"6d56";s:29:"class.tx_seminars_speaker.php";s:4:"a2be";s:32:"class.tx_seminars_speakerbag.php";s:4:"ea00";s:29:"class.tx_seminars_tcemain.php";s:4:"1393";s:36:"class.tx_seminars_templatehelper.php";s:4:"292b";s:30:"class.tx_seminars_timeslot.php";s:4:"27c1";s:33:"class.tx_seminars_timeslotbag.php";s:4:"72d8";s:30:"class.tx_seminars_timespan.php";s:4:"ad59";s:21:"ext_conf_template.txt";s:4:"8b8a";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"7780";s:14:"ext_tables.php";s:4:"7656";s:14:"ext_tables.sql";s:4:"2492";s:19:"flexform_pi1_ds.xml";s:4:"27ca";s:13:"locallang.xml";s:4:"4acc";s:16:"locallang_db.xml";s:4:"5c73";s:13:"seminars.tmpl";s:4:"c90c";s:7:"tca.php";s:4:"e660";s:8:"todo.txt";s:4:"6b5a";s:25:"api/class.tx_seminars.php";s:4:"127d";s:21:"doc/german-manual.sxw";s:4:"9ba5";s:14:"doc/manual.sxw";s:4:"169b";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"775f";s:14:"mod1/index.php";s:4:"5835";s:18:"mod1/locallang.xml";s:4:"e74e";s:22:"mod1/locallang_mod.xml";s:4:"8549";s:19:"mod1/moduleicon.gif";s:4:"8074";s:38:"mod2/class.tx_seminars_backendlist.php";s:4:"c6db";s:30:"mod2/class.tx_seminars_csv.php";s:4:"1342";s:37:"mod2/class.tx_seminars_eventslist.php";s:4:"c6e7";s:41:"mod2/class.tx_seminars_organizerslist.php";s:4:"6729";s:44:"mod2/class.tx_seminars_registrationslist.php";s:4:"6087";s:39:"mod2/class.tx_seminars_speakerslist.php";s:4:"b046";s:13:"mod2/conf.php";s:4:"c9ee";s:14:"mod2/index.php";s:4:"330f";s:18:"mod2/locallang.xml";s:4:"adef";s:22:"mod2/locallang_mod.xml";s:4:"2067";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:38:"pi1/class.tx_seminars_event_editor.php";s:4:"e885";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"6ce9";s:45:"pi1/class.tx_seminars_registration_editor.php";s:4:"fd1f";s:20:"pi1/event_editor.xml";s:4:"ef06";s:17:"pi1/locallang.xml";s:4:"6a00";s:28:"pi1/registration_editor.html";s:4:"d501";s:33:"pi1/registration_editor_step1.xml";s:4:"9a0e";s:33:"pi1/registration_editor_step2.xml";s:4:"5d67";s:42:"pi1/registration_editor_unregistration.xml";s:4:"1ba3";s:20:"pi1/seminars_pi1.css";s:4:"500c";s:21:"pi1/seminars_pi1.tmpl";s:4:"0617";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"f711";s:17:"pi2/locallang.xml";s:4:"4a96";s:20:"static/constants.txt";s:4:"f461";s:16:"static/setup.txt";s:4:"f6dd";s:44:"tests/tx_seminars_dbpluginchild_testcase.php";s:4:"4f42";s:34:"tests/tx_seminars_pi1_testcase.php";s:4:"c6aa";s:48:"tests/tx_seminars_registrationchild_testcase.php";s:4:"6621";s:43:"tests/tx_seminars_seminarchild_testcase.php";s:4:"98d0";s:44:"tests/tx_seminars_timespanchild_testcase.php";s:4:"1d2f";s:50:"tests/fixtures/class.tx_seminars_dbpluginchild.php";s:4:"e132";s:54:"tests/fixtures/class.tx_seminars_registrationchild.php";s:4:"3d45";s:49:"tests/fixtures/class.tx_seminars_seminarchild.php";s:4:"c1df";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"6ca0";s:28:"tests/fixtures/locallang.xml";s:4:"5104";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.1.2-0.0.0',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.4.0-',
			'ameos_formidable' => '0.7.0-0.7.0',
			'static_info_tables' => '2.0.2-',
		),
		'conflicts' => array(
			'dbal' => '',
		),
		'suggests' => array(
			'date2cal' => '',
			'sr_feuser_register' => '',
			'onetimeaccount' => '',
		),
	),
	'suggests' => array(
		'date2cal' => '',
		'sr_feuser_register' => '',
		'onetimeaccount' => '',
	),
);

?>