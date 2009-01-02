<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 02-01-2009 16:59
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
	'dependencies' => 'cms,css_styled_content,oelib,ameos_formidable,static_info_tables',
	'conflicts' => 'dbal,date2cal',
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
	'version' => '0.6.3',
	'_md5_values_when_last_written' => 'a:141:{s:13:"changelog.txt";s:4:"069b";s:25:"class.tx_seminars_bag.php";s:4:"18e1";s:32:"class.tx_seminars_bagbuilder.php";s:4:"464f";s:30:"class.tx_seminars_category.php";s:4:"3194";s:33:"class.tx_seminars_categorybag.php";s:4:"0d82";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"0f47";s:33:"class.tx_seminars_configcheck.php";s:4:"2170";s:34:"class.tx_seminars_configgetter.php";s:4:"9b55";s:30:"class.tx_seminars_dbplugin.php";s:4:"e318";s:34:"class.tx_seminars_objectfromdb.php";s:4:"e1cf";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"74bf";s:31:"class.tx_seminars_organizer.php";s:4:"d329";s:34:"class.tx_seminars_organizerbag.php";s:4:"c977";s:27:"class.tx_seminars_place.php";s:4:"72a9";s:30:"class.tx_seminars_placebag.php";s:4:"5959";s:34:"class.tx_seminars_registration.php";s:4:"7da6";s:37:"class.tx_seminars_registrationbag.php";s:4:"372f";s:41:"class.tx_seminars_registrationmanager.php";s:4:"b901";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"9c72";s:29:"class.tx_seminars_seminar.php";s:4:"b16b";s:32:"class.tx_seminars_seminarbag.php";s:4:"fc0d";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"b59d";s:29:"class.tx_seminars_speaker.php";s:4:"7612";s:32:"class.tx_seminars_speakerbag.php";s:4:"6d6b";s:29:"class.tx_seminars_tcemain.php";s:4:"cefa";s:36:"class.tx_seminars_templatehelper.php";s:4:"ac11";s:30:"class.tx_seminars_timeslot.php";s:4:"9300";s:33:"class.tx_seminars_timeslotbag.php";s:4:"08a5";s:30:"class.tx_seminars_timespan.php";s:4:"4396";s:26:"class.ux_t3lib_tcemain.php";s:4:"7a7a";s:21:"ext_conf_template.txt";s:4:"babc";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"a94c";s:14:"ext_tables.php";s:4:"6c41";s:14:"ext_tables.sql";s:4:"2e10";s:19:"flexform_pi1_ds.xml";s:4:"f6af";s:13:"locallang.xml";s:4:"4c7d";s:16:"locallang_db.xml";s:4:"bfb6";s:13:"seminars.tmpl";s:4:"5f7c";s:7:"tca.php";s:4:"171a";s:8:"todo.txt";s:4:"c250";s:25:"api/class.tx_seminars.php";s:4:"73a2";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1218";s:14:"mod1/index.php";s:4:"4aa5";s:18:"mod1/locallang.xml";s:4:"c867";s:22:"mod1/locallang_mod.xml";s:4:"2d24";s:19:"mod1/moduleicon.gif";s:4:"8074";s:29:"lib/tx_seminars_constants.php";s:4:"8c97";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"bd58";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"c209";s:17:"pi2/locallang.xml";s:4:"ef40";s:20:"static/constants.txt";s:4:"c133";s:16:"static/setup.txt";s:4:"af27";s:39:"tests/tx_seminars_category_testcase.php";s:4:"4d16";s:42:"tests/tx_seminars_categorybag_testcase.php";s:4:"c63b";s:49:"tests/tx_seminars_categorybagbuilder_testcase.php";s:4:"3676";s:44:"tests/tx_seminars_dbpluginchild_testcase.php";s:4:"8507";s:47:"tests/tx_seminars_eventEditorChild_testcase.php";s:4:"f647";s:41:"tests/tx_seminars_eventslist_testcase.php";s:4:"4372";s:40:"tests/tx_seminars_organizer_testcase.php";s:4:"4738";s:43:"tests/tx_seminars_organizerbag_testcase.php";s:4:"413a";s:34:"tests/tx_seminars_pi1_testcase.php";s:4:"d0d1";s:34:"tests/tx_seminars_pi2_testcase.php";s:4:"6840";s:36:"tests/tx_seminars_place_testcase.php";s:4:"4ac9";s:54:"tests/tx_seminars_registrationEditorChild_testcase.php";s:4:"d02b";s:48:"tests/tx_seminars_registrationchild_testcase.php";s:4:"0598";s:50:"tests/tx_seminars_registrationmanager_testcase.php";s:4:"0877";s:41:"tests/tx_seminars_seminarbag_testcase.php";s:4:"2cf1";s:48:"tests/tx_seminars_seminarbagbuilder_testcase.php";s:4:"3f37";s:43:"tests/tx_seminars_seminarchild_testcase.php";s:4:"d4eb";s:38:"tests/tx_seminars_speaker_testcase.php";s:4:"2255";s:41:"tests/tx_seminars_speakerbag_testcase.php";s:4:"1e09";s:35:"tests/tx_seminars_test_testcase.php";s:4:"9bdc";s:38:"tests/tx_seminars_testbag_testcase.php";s:4:"ca9e";s:45:"tests/tx_seminars_testbagbuilder_testcase.php";s:4:"3f27";s:44:"tests/tx_seminars_timeslotchild_testcase.php";s:4:"cff7";s:44:"tests/tx_seminars_timespanchild_testcase.php";s:4:"5911";s:50:"tests/fixtures/class.tx_seminars_dbpluginchild.php";s:4:"2ef8";s:53:"tests/fixtures/class.tx_seminars_eventEditorChild.php";s:4:"0e2e";s:60:"tests/fixtures/class.tx_seminars_registrationEditorChild.php";s:4:"fda4";s:54:"tests/fixtures/class.tx_seminars_registrationchild.php";s:4:"7ea4";s:49:"tests/fixtures/class.tx_seminars_seminarchild.php";s:4:"4e54";s:49:"tests/fixtures/class.tx_seminars_speakerchild.php";s:4:"5c16";s:41:"tests/fixtures/class.tx_seminars_test.php";s:4:"7f48";s:44:"tests/fixtures/class.tx_seminars_testbag.php";s:4:"2082";s:51:"tests/fixtures/class.tx_seminars_testbagbuilder.php";s:4:"011f";s:50:"tests/fixtures/class.tx_seminars_timeslotchild.php";s:4:"c0ae";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"f809";s:28:"tests/fixtures/locallang.xml";s:4:"182e";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"9807";s:14:"doc/manual.sxw";s:4:"df73";s:38:"mod2/class.tx_seminars_backendlist.php";s:4:"8820";s:30:"mod2/class.tx_seminars_csv.php";s:4:"44c9";s:37:"mod2/class.tx_seminars_eventslist.php";s:4:"af4c";s:41:"mod2/class.tx_seminars_organizerslist.php";s:4:"56c0";s:44:"mod2/class.tx_seminars_registrationslist.php";s:4:"dafa";s:39:"mod2/class.tx_seminars_speakerslist.php";s:4:"2613";s:13:"mod2/conf.php";s:4:"212e";s:14:"mod2/index.php";s:4:"d5f9";s:18:"mod2/locallang.xml";s:4:"f931";s:22:"mod2/locallang_mod.xml";s:4:"8362";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:38:"pi1/class.tx_seminars_event_editor.php";s:4:"cc25";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"ed1b";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"5746";s:45:"pi1/class.tx_seminars_registration_editor.php";s:4:"3f7b";s:20:"pi1/event_editor.xml";s:4:"ff84";s:17:"pi1/locallang.xml";s:4:"937c";s:28:"pi1/registration_editor.html";s:4:"5ae0";s:33:"pi1/registration_editor_step1.xml";s:4:"7ace";s:33:"pi1/registration_editor_step2.xml";s:4:"46a6";s:42:"pi1/registration_editor_unregistration.xml";s:4:"1923";s:20:"pi1/seminars_pi1.css";s:4:"d4b1";s:21:"pi1/seminars_pi1.tmpl";s:4:"c856";}',
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
			'date2cal' => '',
		),
		'suggests' => array(
			'onetimeaccount' => '',
			'sr_feuser_register' => '',
		),
	),
	'suggests' => array(
		'onetimeaccount' => '',
		'sr_feuser_register' => '',
	),
);

?>