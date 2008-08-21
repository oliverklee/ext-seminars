<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 12-05-2008 20:19
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
	'dependencies' => 'cms,css_styled_content,oelib,ameos_formidable,partner,static_info_tables,static_info_tables_taxes',
	'conflicts' => 'dbal',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1,mod2',
	'state' => 'alpha',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'fe_users,tx_partner_contact_info,tx_partner_main',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.6.99',
	'_md5_values_when_last_written' => 'a:135:{s:13:"changelog.txt";s:4:"f7c9";s:25:"class.tx_seminars_bag.php";s:4:"b282";s:32:"class.tx_seminars_bagbuilder.php";s:4:"69a9";s:30:"class.tx_seminars_category.php";s:4:"eb6a";s:33:"class.tx_seminars_categorybag.php";s:4:"fde1";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"79f4";s:33:"class.tx_seminars_configcheck.php";s:4:"1545";s:34:"class.tx_seminars_configgetter.php";s:4:"f91a";s:30:"class.tx_seminars_dbplugin.php";s:4:"5a46";s:34:"class.tx_seminars_objectfromdb.php";s:4:"9b0a";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"5b8b";s:31:"class.tx_seminars_organizer.php";s:4:"7bf1";s:34:"class.tx_seminars_organizerbag.php";s:4:"5765";s:27:"class.tx_seminars_place.php";s:4:"f73b";s:30:"class.tx_seminars_placebag.php";s:4:"5d80";s:34:"class.tx_seminars_registration.php";s:4:"1b22";s:37:"class.tx_seminars_registrationbag.php";s:4:"37ac";s:41:"class.tx_seminars_registrationmanager.php";s:4:"64dd";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"de0a";s:29:"class.tx_seminars_seminar.php";s:4:"40db";s:32:"class.tx_seminars_seminarbag.php";s:4:"4a6d";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"108c";s:29:"class.tx_seminars_speaker.php";s:4:"730b";s:32:"class.tx_seminars_speakerbag.php";s:4:"71b4";s:29:"class.tx_seminars_tcemain.php";s:4:"eb81";s:36:"class.tx_seminars_templatehelper.php";s:4:"3f92";s:30:"class.tx_seminars_timeslot.php";s:4:"ab0a";s:33:"class.tx_seminars_timeslotbag.php";s:4:"98c1";s:30:"class.tx_seminars_timespan.php";s:4:"1b50";s:26:"class.ux_t3lib_tcemain.php";s:4:"cf50";s:21:"ext_conf_template.txt";s:4:"f468";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"dc2e";s:14:"ext_tables.php";s:4:"3546";s:14:"ext_tables.sql";s:4:"8af5";s:19:"flexform_pi1_ds.xml";s:4:"e730";s:13:"locallang.xml";s:4:"4c7d";s:16:"locallang_db.xml";s:4:"dfe9";s:13:"seminars.tmpl";s:4:"1718";s:7:"tca.php";s:4:"db44";s:8:"todo.txt";s:4:"c250";s:25:"api/class.tx_seminars.php";s:4:"41d0";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"38cb";s:14:"mod1/index.php";s:4:"9dee";s:18:"mod1/locallang.xml";s:4:"c867";s:22:"mod1/locallang_mod.xml";s:4:"2d24";s:19:"mod1/moduleicon.gif";s:4:"8074";s:29:"lib/tx_seminars_constants.php";s:4:"6448";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"61a5";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"43ca";s:17:"pi2/locallang.xml";s:4:"ef40";s:20:"static/constants.txt";s:4:"c133";s:16:"static/setup.txt";s:4:"45f5";s:39:"tests/tx_seminars_category_testcase.php";s:4:"6235";s:42:"tests/tx_seminars_categorybag_testcase.php";s:4:"c7cc";s:49:"tests/tx_seminars_categorybagbuilder_testcase.php";s:4:"9634";s:44:"tests/tx_seminars_dbpluginchild_testcase.php";s:4:"3570";s:41:"tests/tx_seminars_eventslist_testcase.php";s:4:"b5fa";s:40:"tests/tx_seminars_organizer_testcase.php";s:4:"e887";s:43:"tests/tx_seminars_organizerbag_testcase.php";s:4:"face";s:34:"tests/tx_seminars_pi1_testcase.php";s:4:"7118";s:34:"tests/tx_seminars_pi2_testcase.php";s:4:"eed9";s:36:"tests/tx_seminars_place_testcase.php";s:4:"132f";s:48:"tests/tx_seminars_registrationchild_testcase.php";s:4:"0725";s:41:"tests/tx_seminars_seminarbag_testcase.php";s:4:"f721";s:48:"tests/tx_seminars_seminarbagbuilder_testcase.php";s:4:"f03b";s:43:"tests/tx_seminars_seminarchild_testcase.php";s:4:"4637";s:38:"tests/tx_seminars_speaker_testcase.php";s:4:"318d";s:41:"tests/tx_seminars_speakerbag_testcase.php";s:4:"9d6e";s:35:"tests/tx_seminars_test_testcase.php";s:4:"14c8";s:38:"tests/tx_seminars_testbag_testcase.php";s:4:"a0f0";s:45:"tests/tx_seminars_testbagbuilder_testcase.php";s:4:"32cd";s:44:"tests/tx_seminars_timeslotchild_testcase.php";s:4:"c43f";s:44:"tests/tx_seminars_timespanchild_testcase.php";s:4:"e3c6";s:50:"tests/fixtures/class.tx_seminars_dbpluginchild.php";s:4:"309c";s:54:"tests/fixtures/class.tx_seminars_registrationchild.php";s:4:"f5a4";s:49:"tests/fixtures/class.tx_seminars_seminarchild.php";s:4:"67bc";s:49:"tests/fixtures/class.tx_seminars_speakerchild.php";s:4:"d7fa";s:41:"tests/fixtures/class.tx_seminars_test.php";s:4:"f8db";s:44:"tests/fixtures/class.tx_seminars_testbag.php";s:4:"9d32";s:51:"tests/fixtures/class.tx_seminars_testbagbuilder.php";s:4:"28d4";s:50:"tests/fixtures/class.tx_seminars_timeslotchild.php";s:4:"cc13";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"711b";s:28:"tests/fixtures/locallang.xml";s:4:"182e";s:21:"doc/german-manual.sxw";s:4:"c96f";s:14:"doc/manual.sxw";s:4:"d7ab";s:38:"mod2/class.tx_seminars_backendlist.php";s:4:"4678";s:30:"mod2/class.tx_seminars_csv.php";s:4:"087f";s:37:"mod2/class.tx_seminars_eventslist.php";s:4:"7e06";s:41:"mod2/class.tx_seminars_organizerslist.php";s:4:"3c55";s:44:"mod2/class.tx_seminars_registrationslist.php";s:4:"6124";s:39:"mod2/class.tx_seminars_speakerslist.php";s:4:"0b2c";s:13:"mod2/conf.php";s:4:"5fda";s:14:"mod2/index.php";s:4:"bffd";s:18:"mod2/locallang.xml";s:4:"f931";s:22:"mod2/locallang_mod.xml";s:4:"8362";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:38:"pi1/class.tx_seminars_event_editor.php";s:4:"a162";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"1e09";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"70b6";s:45:"pi1/class.tx_seminars_registration_editor.php";s:4:"b2d0";s:20:"pi1/event_editor.xml";s:4:"ff84";s:17:"pi1/locallang.xml";s:4:"937c";s:28:"pi1/registration_editor.html";s:4:"5ae0";s:33:"pi1/registration_editor_step1.xml";s:4:"7ace";s:33:"pi1/registration_editor_step2.xml";s:4:"46a6";s:42:"pi1/registration_editor_unregistration.xml";s:4:"1923";s:20:"pi1/seminars_pi1.css";s:4:"d4b1";s:21:"pi1/seminars_pi1.tmpl";s:4:"4e66";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.1.2-0.0.0',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.4.1-',
			'ameos_formidable' => '0.7.0-0.7.0',
			'partner' => '0.5.0-',
			'static_info_tables' => '2.0.8-',
			'static_info_tables_taxes' => '',
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