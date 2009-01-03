<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 04-01-2009 00:57
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
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.7.0',
	'_md5_values_when_last_written' => 'a:161:{s:13:"changelog.txt";s:4:"b4f9";s:25:"class.tx_seminars_bag.php";s:4:"97d1";s:32:"class.tx_seminars_bagbuilder.php";s:4:"4404";s:30:"class.tx_seminars_category.php";s:4:"d5f4";s:33:"class.tx_seminars_categorybag.php";s:4:"6330";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"6799";s:33:"class.tx_seminars_configcheck.php";s:4:"26f0";s:34:"class.tx_seminars_configgetter.php";s:4:"8205";s:31:"class.tx_seminars_flexForms.php";s:4:"b92e";s:34:"class.tx_seminars_objectfromdb.php";s:4:"776c";s:31:"class.tx_seminars_organizer.php";s:4:"cc4e";s:34:"class.tx_seminars_organizerbag.php";s:4:"2501";s:27:"class.tx_seminars_place.php";s:4:"4ecc";s:30:"class.tx_seminars_placebag.php";s:4:"e7b1";s:34:"class.tx_seminars_registration.php";s:4:"5ad2";s:44:"class.tx_seminars_registrationBagBuilder.php";s:4:"6396";s:37:"class.tx_seminars_registrationbag.php";s:4:"b762";s:41:"class.tx_seminars_registrationmanager.php";s:4:"8cb2";s:29:"class.tx_seminars_seminar.php";s:4:"dbb6";s:32:"class.tx_seminars_seminarbag.php";s:4:"cab7";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"2c4a";s:29:"class.tx_seminars_speaker.php";s:4:"ebfa";s:32:"class.tx_seminars_speakerbag.php";s:4:"d698";s:29:"class.tx_seminars_tcemain.php";s:4:"5e33";s:30:"class.tx_seminars_timeslot.php";s:4:"ebe7";s:33:"class.tx_seminars_timeslotbag.php";s:4:"2caf";s:30:"class.tx_seminars_timespan.php";s:4:"b7b4";s:26:"class.ux_t3lib_tcemain.php";s:4:"c95b";s:21:"ext_conf_template.txt";s:4:"babc";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"d132";s:14:"ext_tables.php";s:4:"0915";s:14:"ext_tables.sql";s:4:"8e08";s:19:"flexform_pi1_ds.xml";s:4:"3362";s:13:"locallang.xml";s:4:"6d0e";s:26:"locallang_csh_seminars.xml";s:4:"a3cc";s:16:"locallang_db.xml";s:4:"3c7f";s:13:"seminars.tmpl";s:4:"5f7c";s:7:"tca.php";s:4:"bb55";s:8:"todo.txt";s:4:"c250";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1218";s:14:"mod1/index.php";s:4:"1d29";s:18:"mod1/locallang.xml";s:4:"c867";s:22:"mod1/locallang_mod.xml";s:4:"2d24";s:19:"mod1/moduleicon.gif";s:4:"8074";s:29:"lib/tx_seminars_constants.php";s:4:"e7f1";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:33:"icons/icon_tx_seminars_prices.gif";s:4:"61a5";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"bd58";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"6808";s:17:"pi2/locallang.xml";s:4:"ef40";s:20:"static/constants.txt";s:4:"c133";s:16:"static/setup.txt";s:4:"9471";s:39:"tests/tx_seminars_category_testcase.php";s:4:"6d77";s:42:"tests/tx_seminars_categorybag_testcase.php";s:4:"8b68";s:49:"tests/tx_seminars_categorybagbuilder_testcase.php";s:4:"3aef";s:47:"tests/tx_seminars_eventEditorChild_testcase.php";s:4:"eb2c";s:41:"tests/tx_seminars_eventslist_testcase.php";s:4:"c087";s:51:"tests/tx_seminars_frontEndCategoryList_testcase.php";s:4:"a6ad";s:48:"tests/tx_seminars_frontEndCountdown_testcase.php";s:4:"ab5c";s:45:"tests/tx_seminars_frontEndEditor_testcase.php";s:4:"564c";s:52:"tests/tx_seminars_frontEndEventHeadline_testcase.php";s:4:"1023";s:56:"tests/tx_seminars_frontEndRegistrationsList_testcase.php";s:4:"8a2e";s:53:"tests/tx_seminars_frontEndSelectorWidget_testcase.php";s:4:"f21b";s:49:"tests/tx_seminars_mod2_BackEndModule_testcase.php";s:4:"7ae5";s:40:"tests/tx_seminars_organizer_testcase.php";s:4:"8028";s:43:"tests/tx_seminars_organizerbag_testcase.php";s:4:"27c0";s:59:"tests/tx_seminars_pi1_frontEndRequirementsList_testcase.php";s:4:"5621";s:34:"tests/tx_seminars_pi1_testcase.php";s:4:"e11e";s:34:"tests/tx_seminars_pi2_testcase.php";s:4:"a20d";s:36:"tests/tx_seminars_place_testcase.php";s:4:"736f";s:53:"tests/tx_seminars_registrationBagBuilder_testcase.php";s:4:"f5e1";s:54:"tests/tx_seminars_registrationEditorChild_testcase.php";s:4:"4880";s:48:"tests/tx_seminars_registrationchild_testcase.php";s:4:"0f05";s:50:"tests/tx_seminars_registrationmanager_testcase.php";s:4:"31e9";s:48:"tests/tx_seminars_registrationslist_testcase.php";s:4:"5db8";s:41:"tests/tx_seminars_seminarbag_testcase.php";s:4:"392f";s:48:"tests/tx_seminars_seminarbagbuilder_testcase.php";s:4:"ae0f";s:43:"tests/tx_seminars_seminarchild_testcase.php";s:4:"b80f";s:38:"tests/tx_seminars_speaker_testcase.php";s:4:"3ee8";s:41:"tests/tx_seminars_speakerbag_testcase.php";s:4:"0a82";s:35:"tests/tx_seminars_test_testcase.php";s:4:"6107";s:38:"tests/tx_seminars_testbag_testcase.php";s:4:"5c06";s:45:"tests/tx_seminars_testbagbuilder_testcase.php";s:4:"e5e5";s:50:"tests/tx_seminars_testingFrondEndView_testcase.php";s:4:"595e";s:44:"tests/tx_seminars_timeslotchild_testcase.php";s:4:"7db3";s:44:"tests/tx_seminars_timespanchild_testcase.php";s:4:"46be";s:60:"tests/fixtures/class.tx_seminars_brokenTestingBagBuilder.php";s:4:"0e1b";s:53:"tests/fixtures/class.tx_seminars_eventEditorChild.php";s:4:"5f5f";s:60:"tests/fixtures/class.tx_seminars_registrationEditorChild.php";s:4:"bf29";s:54:"tests/fixtures/class.tx_seminars_registrationchild.php";s:4:"fe3a";s:49:"tests/fixtures/class.tx_seminars_seminarchild.php";s:4:"0a88";s:49:"tests/fixtures/class.tx_seminars_speakerchild.php";s:4:"e6cc";s:41:"tests/fixtures/class.tx_seminars_test.php";s:4:"b6d1";s:44:"tests/fixtures/class.tx_seminars_testbag.php";s:4:"0e13";s:51:"tests/fixtures/class.tx_seminars_testbagbuilder.php";s:4:"1769";s:56:"tests/fixtures/class.tx_seminars_testingFrontEndView.php";s:4:"2f3e";s:50:"tests/fixtures/class.tx_seminars_timeslotchild.php";s:4:"a5bc";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"615a";s:28:"tests/fixtures/locallang.xml";s:4:"182e";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"f94b";s:14:"doc/manual.sxw";s:4:"9088";s:45:"mod2/class.tx_seminars_mod2_BackEndModule.php";s:4:"2dfb";s:43:"mod2/class.tx_seminars_mod2_backendlist.php";s:4:"01e0";s:35:"mod2/class.tx_seminars_mod2_csv.php";s:4:"0a65";s:42:"mod2/class.tx_seminars_mod2_eventslist.php";s:4:"e7db";s:46:"mod2/class.tx_seminars_mod2_organizerslist.php";s:4:"5af4";s:49:"mod2/class.tx_seminars_mod2_registrationslist.php";s:4:"7306";s:44:"mod2/class.tx_seminars_mod2_speakerslist.php";s:4:"a0ea";s:13:"mod2/conf.php";s:4:"212e";s:14:"mod2/index.php";s:4:"5771";s:18:"mod2/locallang.xml";s:4:"170b";s:22:"mod2/locallang_mod.xml";s:4:"8362";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"1326";s:41:"pi1/class.tx_seminars_pi1_eventEditor.php";s:4:"5da1";s:50:"pi1/class.tx_seminars_pi1_frontEndCategoryList.php";s:4:"0dce";s:47:"pi1/class.tx_seminars_pi1_frontEndCountdown.php";s:4:"bc07";s:44:"pi1/class.tx_seminars_pi1_frontEndEditor.php";s:4:"5b6b";s:51:"pi1/class.tx_seminars_pi1_frontEndEventHeadline.php";s:4:"8fdc";s:55:"pi1/class.tx_seminars_pi1_frontEndRegistrationsList.php";s:4:"5a03";s:54:"pi1/class.tx_seminars_pi1_frontEndRequirementsList.php";s:4:"41cf";s:52:"pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php";s:4:"1335";s:42:"pi1/class.tx_seminars_pi1_frontEndView.php";s:4:"0ba9";s:48:"pi1/class.tx_seminars_pi1_registrationEditor.php";s:4:"90e9";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"2086";s:20:"pi1/event_editor.xml";s:4:"dd70";s:17:"pi1/locallang.xml";s:4:"5d47";s:28:"pi1/registration_editor.html";s:4:"f4d7";s:33:"pi1/registration_editor_step1.xml";s:4:"df91";s:33:"pi1/registration_editor_step2.xml";s:4:"bd21";s:42:"pi1/registration_editor_unregistration.xml";s:4:"a7d2";s:20:"pi1/seminars_pi1.css";s:4:"ccad";s:21:"pi1/seminars_pi1.tmpl";s:4:"51eb";s:22:"pi1/tx_seminars_pi1.js";s:4:"cab2";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.2-0.0.0',
			'typo3' => '4.1.2-0.0.0',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.5.1-',
			'ameos_formidable' => '1.1.0-1.9.99',
			'static_info_tables' => '2.0.8-',
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