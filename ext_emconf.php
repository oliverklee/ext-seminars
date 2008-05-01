<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 13-04-2008 13:14
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
	'dependencies' => 'ameos_formidable,cms,css_styled_content,oelib,static_info_tables',
	'conflicts' => 'dbal',
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
	'_md5_values_when_last_written' => 'a:135:{s:13:"changelog.txt";s:4:"468c";s:25:"class.tx_seminars_bag.php";s:4:"85d5";s:32:"class.tx_seminars_bagbuilder.php";s:4:"599c";s:30:"class.tx_seminars_category.php";s:4:"0868";s:33:"class.tx_seminars_categorybag.php";s:4:"7458";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"3e9a";s:33:"class.tx_seminars_configcheck.php";s:4:"df08";s:34:"class.tx_seminars_configgetter.php";s:4:"0dc0";s:30:"class.tx_seminars_dbplugin.php";s:4:"73c8";s:34:"class.tx_seminars_objectfromdb.php";s:4:"4e8e";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"fdae";s:31:"class.tx_seminars_organizer.php";s:4:"65e4";s:34:"class.tx_seminars_organizerbag.php";s:4:"7407";s:27:"class.tx_seminars_place.php";s:4:"6214";s:30:"class.tx_seminars_placebag.php";s:4:"e1b5";s:34:"class.tx_seminars_registration.php";s:4:"089c";s:37:"class.tx_seminars_registrationbag.php";s:4:"50e6";s:41:"class.tx_seminars_registrationmanager.php";s:4:"29e3";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"d59b";s:29:"class.tx_seminars_seminar.php";s:4:"1a92";s:32:"class.tx_seminars_seminarbag.php";s:4:"b26e";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"df45";s:29:"class.tx_seminars_speaker.php";s:4:"3f89";s:32:"class.tx_seminars_speakerbag.php";s:4:"2390";s:29:"class.tx_seminars_tcemain.php";s:4:"ffc7";s:36:"class.tx_seminars_templatehelper.php";s:4:"e433";s:30:"class.tx_seminars_timeslot.php";s:4:"d107";s:33:"class.tx_seminars_timeslotbag.php";s:4:"b33b";s:30:"class.tx_seminars_timespan.php";s:4:"043b";s:26:"class.ux_t3lib_tcemain.php";s:4:"97e0";s:21:"ext_conf_template.txt";s:4:"f468";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"9ea0";s:14:"ext_tables.php";s:4:"e1b3";s:14:"ext_tables.sql";s:4:"2188";s:19:"flexform_pi1_ds.xml";s:4:"a2b1";s:13:"locallang.xml";s:4:"2b1f";s:16:"locallang_db.xml";s:4:"92ad";s:13:"seminars.tmpl";s:4:"fa32";s:7:"tca.php";s:4:"4132";s:8:"todo.txt";s:4:"0013";s:25:"api/class.tx_seminars.php";s:4:"2fa5";s:21:"doc/german-manual.sxw";s:4:"3344";s:14:"doc/manual.sxw";s:4:"03df";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"61a5";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"lib/tx_seminars_constants.php";s:4:"438f";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"775f";s:14:"mod1/index.php";s:4:"437e";s:18:"mod1/locallang.xml";s:4:"5ad2";s:22:"mod1/locallang_mod.xml";s:4:"4a38";s:19:"mod1/moduleicon.gif";s:4:"8074";s:38:"mod2/class.tx_seminars_backendlist.php";s:4:"9884";s:30:"mod2/class.tx_seminars_csv.php";s:4:"c22f";s:37:"mod2/class.tx_seminars_eventslist.php";s:4:"fc8b";s:41:"mod2/class.tx_seminars_organizerslist.php";s:4:"ad03";s:44:"mod2/class.tx_seminars_registrationslist.php";s:4:"3f0a";s:39:"mod2/class.tx_seminars_speakerslist.php";s:4:"f6db";s:13:"mod2/conf.php";s:4:"c9ee";s:14:"mod2/index.php";s:4:"f650";s:18:"mod2/locallang.xml";s:4:"5a1a";s:22:"mod2/locallang_mod.xml";s:4:"60c7";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:38:"pi1/class.tx_seminars_event_editor.php";s:4:"0663";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"b71a";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"af95";s:45:"pi1/class.tx_seminars_registration_editor.php";s:4:"c000";s:20:"pi1/event_editor.xml";s:4:"ff84";s:17:"pi1/locallang.xml";s:4:"7ca9";s:28:"pi1/registration_editor.html";s:4:"5ae0";s:33:"pi1/registration_editor_step1.xml";s:4:"7ace";s:33:"pi1/registration_editor_step2.xml";s:4:"46a6";s:42:"pi1/registration_editor_unregistration.xml";s:4:"1923";s:20:"pi1/seminars_pi1.css";s:4:"695f";s:21:"pi1/seminars_pi1.tmpl";s:4:"6e6c";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"6585";s:17:"pi2/locallang.xml";s:4:"ef40";s:20:"static/constants.txt";s:4:"c133";s:16:"static/setup.txt";s:4:"bedc";s:39:"tests/tx_seminars_category_testcase.php";s:4:"9346";s:42:"tests/tx_seminars_categorybag_testcase.php";s:4:"9fe1";s:49:"tests/tx_seminars_categorybagbuilder_testcase.php";s:4:"e0dd";s:44:"tests/tx_seminars_dbpluginchild_testcase.php";s:4:"2897";s:41:"tests/tx_seminars_eventslist_testcase.php";s:4:"c718";s:40:"tests/tx_seminars_organizer_testcase.php";s:4:"6bfc";s:43:"tests/tx_seminars_organizerbag_testcase.php";s:4:"1787";s:34:"tests/tx_seminars_pi1_testcase.php";s:4:"1f1f";s:34:"tests/tx_seminars_pi2_testcase.php";s:4:"3221";s:36:"tests/tx_seminars_place_testcase.php";s:4:"6732";s:48:"tests/tx_seminars_registrationchild_testcase.php";s:4:"9198";s:41:"tests/tx_seminars_seminarbag_testcase.php";s:4:"5e23";s:48:"tests/tx_seminars_seminarbagbuilder_testcase.php";s:4:"019f";s:43:"tests/tx_seminars_seminarchild_testcase.php";s:4:"3ff0";s:38:"tests/tx_seminars_speaker_testcase.php";s:4:"bf28";s:41:"tests/tx_seminars_speakerbag_testcase.php";s:4:"ff5f";s:35:"tests/tx_seminars_test_testcase.php";s:4:"9e17";s:38:"tests/tx_seminars_testbag_testcase.php";s:4:"c0da";s:45:"tests/tx_seminars_testbagbuilder_testcase.php";s:4:"3225";s:44:"tests/tx_seminars_timeslotchild_testcase.php";s:4:"ff30";s:44:"tests/tx_seminars_timespanchild_testcase.php";s:4:"653f";s:50:"tests/fixtures/class.tx_seminars_dbpluginchild.php";s:4:"39bf";s:54:"tests/fixtures/class.tx_seminars_registrationchild.php";s:4:"97c3";s:49:"tests/fixtures/class.tx_seminars_seminarchild.php";s:4:"23a2";s:49:"tests/fixtures/class.tx_seminars_speakerchild.php";s:4:"a01e";s:41:"tests/fixtures/class.tx_seminars_test.php";s:4:"4b3f";s:44:"tests/fixtures/class.tx_seminars_testbag.php";s:4:"272a";s:51:"tests/fixtures/class.tx_seminars_testbagbuilder.php";s:4:"7b27";s:50:"tests/fixtures/class.tx_seminars_timeslotchild.php";s:4:"864e";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"8a81";s:28:"tests/fixtures/locallang.xml";s:4:"182e";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.1.2-0.0.0',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.4.0-',
			'ameos_formidable' => '0.7.0-0.7.0',
			'static_info_tables' => '2.0.2-'
		),
		'conflicts' => array(
			'dbal' => '',
		),
		'suggests' => array(
			'date2cal' => '',
			'onetimeaccount' => '',
			'sr_feuser_register' => '',
		),
	),
	'suggests' => array(
		'date2cal' => '',
		'onetimeaccount' => '',
		'sr_feuser_register' => '',
	),
);

?>