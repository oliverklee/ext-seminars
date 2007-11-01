<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 01-11-2007 22:49
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
	'version' => '0.5.3',
	'_md5_values_when_last_written' => 'a:95:{s:13:"changelog.txt";s:4:"b9c1";s:25:"class.tx_seminars_bag.php";s:4:"2614";s:33:"class.tx_seminars_configcheck.php";s:4:"093a";s:34:"class.tx_seminars_configgetter.php";s:4:"fb3b";s:30:"class.tx_seminars_dbplugin.php";s:4:"7ebc";s:34:"class.tx_seminars_objectfromdb.php";s:4:"70d0";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"fae6";s:31:"class.tx_seminars_organizer.php";s:4:"1d92";s:34:"class.tx_seminars_organizerbag.php";s:4:"7649";s:27:"class.tx_seminars_place.php";s:4:"5648";s:30:"class.tx_seminars_placebag.php";s:4:"d743";s:34:"class.tx_seminars_registration.php";s:4:"5a5f";s:37:"class.tx_seminars_registrationbag.php";s:4:"49a5";s:41:"class.tx_seminars_registrationmanager.php";s:4:"a282";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"070c";s:29:"class.tx_seminars_seminar.php";s:4:"cca6";s:32:"class.tx_seminars_seminarbag.php";s:4:"d682";s:29:"class.tx_seminars_speaker.php";s:4:"9f05";s:32:"class.tx_seminars_speakerbag.php";s:4:"028f";s:29:"class.tx_seminars_tcemain.php";s:4:"0af1";s:36:"class.tx_seminars_templatehelper.php";s:4:"3017";s:30:"class.tx_seminars_timeslot.php";s:4:"8063";s:30:"class.tx_seminars_timespan.php";s:4:"e58f";s:21:"ext_conf_template.txt";s:4:"8b8a";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"2297";s:14:"ext_tables.php";s:4:"1e84";s:14:"ext_tables.sql";s:4:"7364";s:19:"flexform_pi1_ds.xml";s:4:"5c71";s:13:"locallang.xml";s:4:"01c5";s:16:"locallang_db.xml";s:4:"0b68";s:13:"seminars.tmpl";s:4:"a334";s:7:"tca.php";s:4:"f5ec";s:8:"todo.txt";s:4:"cc6e";s:25:"api/class.tx_seminars.php";s:4:"3f28";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"07e2";s:14:"mod1/index.php";s:4:"5544";s:18:"mod1/locallang.xml";s:4:"2cda";s:22:"mod1/locallang_mod.xml";s:4:"3176";s:19:"mod1/moduleicon.gif";s:4:"8074";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"2cb2";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"f179";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"91be";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"5c02";s:17:"pi2/locallang.xml";s:4:"1a29";s:20:"static/constants.txt";s:4:"f461";s:16:"static/setup.txt";s:4:"8155";s:40:"static/tx_srfeuserregister_pi1_tmpl.tmpl";s:4:"7ea9";s:44:"tests/tx_seminars_dbpluginchild_testcase.php";s:4:"4f42";s:34:"tests/tx_seminars_pi1_testcase.php";s:4:"c6aa";s:44:"tests/tx_seminars_timespanchild_testcase.php";s:4:"1d2f";s:50:"tests/fixtures/class.tx_seminars_dbpluginchild.php";s:4:"f44a";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"6ca0";s:28:"tests/fixtures/locallang.xml";s:4:"0837";s:21:"doc/german-manual.sxw";s:4:"83c7";s:14:"doc/manual.sxw";s:4:"bad4";s:38:"mod2/class.tx_seminars_backendlist.php";s:4:"9a27";s:30:"mod2/class.tx_seminars_csv.php";s:4:"1342";s:37:"mod2/class.tx_seminars_eventslist.php";s:4:"9132";s:41:"mod2/class.tx_seminars_organizerslist.php";s:4:"061f";s:44:"mod2/class.tx_seminars_registrationslist.php";s:4:"a5e1";s:39:"mod2/class.tx_seminars_speakerslist.php";s:4:"d359";s:13:"mod2/conf.php";s:4:"90ec";s:14:"mod2/index.php";s:4:"a6cd";s:18:"mod2/locallang.xml";s:4:"874a";s:22:"mod2/locallang_mod.xml";s:4:"dfd4";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:38:"pi1/class.tx_seminars_event_editor.php";s:4:"a55f";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"5011";s:45:"pi1/class.tx_seminars_registration_editor.php";s:4:"cc32";s:20:"pi1/event_editor.xml";s:4:"885b";s:17:"pi1/locallang.xml";s:4:"80e6";s:28:"pi1/registration_editor.html";s:4:"f1f6";s:27:"pi1/registration_editor.xml";s:4:"9a0e";s:33:"pi1/registration_editor_step2.xml";s:4:"5d67";s:20:"pi1/seminars_pi1.css";s:4:"88fb";s:21:"pi1/seminars_pi1.tmpl";s:4:"f988";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'css_styled_content' => '',
			'ameos_formidable' => '0.7.0-0.7.0',
			'static_info_tables' => '',
			'php' => '4.0.0-0.0.0',
			'typo3' => '3.8.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'erotea_date2cal' => '',
			'newloginbox' => '',
			'static_info_tables' => '2.0.2-',
		),
	),
	'suggests' => array(
	),
);

?>