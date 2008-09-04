<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 04-09-2008 19:23
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
	'version' => '0.6.2',
	'_md5_values_when_last_written' => 'a:141:{s:13:"changelog.txt";s:4:"7a7c";s:25:"class.tx_seminars_bag.php";s:4:"280e";s:32:"class.tx_seminars_bagbuilder.php";s:4:"9a38";s:30:"class.tx_seminars_category.php";s:4:"abf6";s:33:"class.tx_seminars_categorybag.php";s:4:"5eeb";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"ab45";s:33:"class.tx_seminars_configcheck.php";s:4:"866b";s:34:"class.tx_seminars_configgetter.php";s:4:"2366";s:30:"class.tx_seminars_dbplugin.php";s:4:"b898";s:34:"class.tx_seminars_objectfromdb.php";s:4:"ab81";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"b3be";s:31:"class.tx_seminars_organizer.php";s:4:"68bc";s:34:"class.tx_seminars_organizerbag.php";s:4:"ee46";s:27:"class.tx_seminars_place.php";s:4:"5053";s:30:"class.tx_seminars_placebag.php";s:4:"176d";s:34:"class.tx_seminars_registration.php";s:4:"f8cb";s:37:"class.tx_seminars_registrationbag.php";s:4:"6b00";s:41:"class.tx_seminars_registrationmanager.php";s:4:"b3e9";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"5063";s:29:"class.tx_seminars_seminar.php";s:4:"cbc6";s:32:"class.tx_seminars_seminarbag.php";s:4:"1bd7";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"6d22";s:29:"class.tx_seminars_speaker.php";s:4:"077c";s:32:"class.tx_seminars_speakerbag.php";s:4:"7604";s:29:"class.tx_seminars_tcemain.php";s:4:"c802";s:36:"class.tx_seminars_templatehelper.php";s:4:"0352";s:30:"class.tx_seminars_timeslot.php";s:4:"e0e5";s:33:"class.tx_seminars_timeslotbag.php";s:4:"3600";s:30:"class.tx_seminars_timespan.php";s:4:"a80d";s:26:"class.ux_t3lib_tcemain.php";s:4:"5dc5";s:21:"ext_conf_template.txt";s:4:"babc";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"8bf2";s:14:"ext_tables.php";s:4:"6c41";s:14:"ext_tables.sql";s:4:"1031";s:19:"flexform_pi1_ds.xml";s:4:"f6af";s:13:"locallang.xml";s:4:"4c7d";s:16:"locallang_db.xml";s:4:"dfe9";s:13:"seminars.tmpl";s:4:"1718";s:7:"tca.php";s:4:"7f75";s:8:"todo.txt";s:4:"c250";s:25:"api/class.tx_seminars.php";s:4:"b7e6";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1218";s:14:"mod1/index.php";s:4:"8320";s:18:"mod1/locallang.xml";s:4:"c867";s:22:"mod1/locallang_mod.xml";s:4:"2d24";s:19:"mod1/moduleicon.gif";s:4:"8074";s:29:"lib/tx_seminars_constants.php";s:4:"ca72";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"bd58";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"215e";s:17:"pi2/locallang.xml";s:4:"ef40";s:20:"static/constants.txt";s:4:"c133";s:16:"static/setup.txt";s:4:"af27";s:39:"tests/tx_seminars_category_testcase.php";s:4:"af78";s:42:"tests/tx_seminars_categorybag_testcase.php";s:4:"6f8e";s:49:"tests/tx_seminars_categorybagbuilder_testcase.php";s:4:"5897";s:44:"tests/tx_seminars_dbpluginchild_testcase.php";s:4:"1ec6";s:47:"tests/tx_seminars_eventEditorChild_testcase.php";s:4:"9c4b";s:41:"tests/tx_seminars_eventslist_testcase.php";s:4:"dc23";s:40:"tests/tx_seminars_organizer_testcase.php";s:4:"687d";s:43:"tests/tx_seminars_organizerbag_testcase.php";s:4:"0886";s:34:"tests/tx_seminars_pi1_testcase.php";s:4:"9c19";s:34:"tests/tx_seminars_pi2_testcase.php";s:4:"0ce6";s:36:"tests/tx_seminars_place_testcase.php";s:4:"c662";s:54:"tests/tx_seminars_registrationEditorChild_testcase.php";s:4:"d793";s:48:"tests/tx_seminars_registrationchild_testcase.php";s:4:"a60a";s:50:"tests/tx_seminars_registrationmanager_testcase.php";s:4:"0372";s:41:"tests/tx_seminars_seminarbag_testcase.php";s:4:"bc92";s:48:"tests/tx_seminars_seminarbagbuilder_testcase.php";s:4:"e926";s:43:"tests/tx_seminars_seminarchild_testcase.php";s:4:"e160";s:38:"tests/tx_seminars_speaker_testcase.php";s:4:"2ef3";s:41:"tests/tx_seminars_speakerbag_testcase.php";s:4:"809f";s:35:"tests/tx_seminars_test_testcase.php";s:4:"3d4e";s:38:"tests/tx_seminars_testbag_testcase.php";s:4:"aa31";s:45:"tests/tx_seminars_testbagbuilder_testcase.php";s:4:"3ac6";s:44:"tests/tx_seminars_timeslotchild_testcase.php";s:4:"f837";s:44:"tests/tx_seminars_timespanchild_testcase.php";s:4:"084f";s:50:"tests/fixtures/class.tx_seminars_dbpluginchild.php";s:4:"c526";s:53:"tests/fixtures/class.tx_seminars_eventEditorChild.php";s:4:"57f4";s:60:"tests/fixtures/class.tx_seminars_registrationEditorChild.php";s:4:"e496";s:54:"tests/fixtures/class.tx_seminars_registrationchild.php";s:4:"6c81";s:49:"tests/fixtures/class.tx_seminars_seminarchild.php";s:4:"d47e";s:49:"tests/fixtures/class.tx_seminars_speakerchild.php";s:4:"b324";s:41:"tests/fixtures/class.tx_seminars_test.php";s:4:"2cf1";s:44:"tests/fixtures/class.tx_seminars_testbag.php";s:4:"3bde";s:51:"tests/fixtures/class.tx_seminars_testbagbuilder.php";s:4:"a803";s:50:"tests/fixtures/class.tx_seminars_timeslotchild.php";s:4:"951d";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"f4a6";s:28:"tests/fixtures/locallang.xml";s:4:"182e";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"9561";s:14:"doc/manual.sxw";s:4:"4b3c";s:38:"mod2/class.tx_seminars_backendlist.php";s:4:"af25";s:30:"mod2/class.tx_seminars_csv.php";s:4:"41a6";s:37:"mod2/class.tx_seminars_eventslist.php";s:4:"bb29";s:41:"mod2/class.tx_seminars_organizerslist.php";s:4:"f4f3";s:44:"mod2/class.tx_seminars_registrationslist.php";s:4:"17ce";s:39:"mod2/class.tx_seminars_speakerslist.php";s:4:"8647";s:13:"mod2/conf.php";s:4:"212e";s:14:"mod2/index.php";s:4:"8cce";s:18:"mod2/locallang.xml";s:4:"f931";s:22:"mod2/locallang_mod.xml";s:4:"8362";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:38:"pi1/class.tx_seminars_event_editor.php";s:4:"a264";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"939c";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"046d";s:45:"pi1/class.tx_seminars_registration_editor.php";s:4:"63dd";s:20:"pi1/event_editor.xml";s:4:"ff84";s:17:"pi1/locallang.xml";s:4:"937c";s:28:"pi1/registration_editor.html";s:4:"5ae0";s:33:"pi1/registration_editor_step1.xml";s:4:"7ace";s:33:"pi1/registration_editor_step2.xml";s:4:"46a6";s:42:"pi1/registration_editor_unregistration.xml";s:4:"1923";s:20:"pi1/seminars_pi1.css";s:4:"d4b1";s:21:"pi1/seminars_pi1.tmpl";s:4:"57c0";}',
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