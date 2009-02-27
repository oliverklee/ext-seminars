<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 27-02-2009 17:07
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
	'version' => '0.7.3',
	'_md5_values_when_last_written' => 'a:114:{s:13:"changelog.txt";s:4:"1955";s:25:"class.tx_seminars_bag.php";s:4:"6197";s:32:"class.tx_seminars_bagbuilder.php";s:4:"4404";s:30:"class.tx_seminars_category.php";s:4:"b216";s:33:"class.tx_seminars_categorybag.php";s:4:"6330";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"6799";s:33:"class.tx_seminars_configcheck.php";s:4:"26f0";s:34:"class.tx_seminars_configgetter.php";s:4:"8205";s:31:"class.tx_seminars_flexForms.php";s:4:"b92e";s:34:"class.tx_seminars_objectfromdb.php";s:4:"1b6c";s:31:"class.tx_seminars_organizer.php";s:4:"0976";s:34:"class.tx_seminars_organizerbag.php";s:4:"be65";s:27:"class.tx_seminars_place.php";s:4:"80d4";s:30:"class.tx_seminars_placebag.php";s:4:"8744";s:34:"class.tx_seminars_registration.php";s:4:"b0bb";s:44:"class.tx_seminars_registrationBagBuilder.php";s:4:"0d32";s:37:"class.tx_seminars_registrationbag.php";s:4:"6d3e";s:41:"class.tx_seminars_registrationmanager.php";s:4:"5780";s:29:"class.tx_seminars_seminar.php";s:4:"a83a";s:32:"class.tx_seminars_seminarbag.php";s:4:"b398";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"e55b";s:29:"class.tx_seminars_speaker.php";s:4:"cdac";s:32:"class.tx_seminars_speakerbag.php";s:4:"163f";s:29:"class.tx_seminars_tcemain.php";s:4:"8b9b";s:30:"class.tx_seminars_timeslot.php";s:4:"e9da";s:33:"class.tx_seminars_timeslotbag.php";s:4:"bea9";s:30:"class.tx_seminars_timespan.php";s:4:"b7b4";s:26:"class.ux_t3lib_tcemain.php";s:4:"c95b";s:21:"ext_conf_template.txt";s:4:"babc";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"d132";s:14:"ext_tables.php";s:4:"0915";s:14:"ext_tables.sql";s:4:"b378";s:19:"flexform_pi1_ds.xml";s:4:"3362";s:13:"locallang.xml";s:4:"6d0e";s:26:"locallang_csh_seminars.xml";s:4:"a3cc";s:16:"locallang_db.xml";s:4:"3c7f";s:13:"seminars.tmpl";s:4:"5f7c";s:7:"tca.php";s:4:"bb55";s:8:"todo.txt";s:4:"c250";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1218";s:14:"mod1/index.php";s:4:"7f78";s:18:"mod1/locallang.xml";s:4:"c867";s:22:"mod1/locallang_mod.xml";s:4:"2d24";s:19:"mod1/moduleicon.gif";s:4:"8074";s:29:"lib/tx_seminars_constants.php";s:4:"79a1";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:33:"icons/icon_tx_seminars_prices.gif";s:4:"61a5";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"bd58";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"6809";s:17:"pi2/locallang.xml";s:4:"ef40";s:20:"static/constants.txt";s:4:"c133";s:16:"static/setup.txt";s:4:"334a";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"02c7";s:14:"doc/manual.sxw";s:4:"9161";s:45:"mod2/class.tx_seminars_mod2_BackEndModule.php";s:4:"2dfb";s:43:"mod2/class.tx_seminars_mod2_backendlist.php";s:4:"01e0";s:35:"mod2/class.tx_seminars_mod2_csv.php";s:4:"0a65";s:42:"mod2/class.tx_seminars_mod2_eventslist.php";s:4:"5334";s:46:"mod2/class.tx_seminars_mod2_organizerslist.php";s:4:"5919";s:49:"mod2/class.tx_seminars_mod2_registrationslist.php";s:4:"d3d3";s:44:"mod2/class.tx_seminars_mod2_speakerslist.php";s:4:"a0ea";s:13:"mod2/conf.php";s:4:"212e";s:14:"mod2/index.php";s:4:"5771";s:18:"mod2/locallang.xml";s:4:"922d";s:22:"mod2/locallang_mod.xml";s:4:"8362";s:13:"mod2/mod2.css";s:4:"ff87";s:19:"mod2/moduleicon.gif";s:4:"032e";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"7e41";s:41:"pi1/class.tx_seminars_pi1_eventEditor.php";s:4:"7309";s:50:"pi1/class.tx_seminars_pi1_frontEndCategoryList.php";s:4:"9706";s:47:"pi1/class.tx_seminars_pi1_frontEndCountdown.php";s:4:"bc07";s:44:"pi1/class.tx_seminars_pi1_frontEndEditor.php";s:4:"5b6b";s:51:"pi1/class.tx_seminars_pi1_frontEndEventHeadline.php";s:4:"8fdc";s:55:"pi1/class.tx_seminars_pi1_frontEndRegistrationsList.php";s:4:"3e54";s:54:"pi1/class.tx_seminars_pi1_frontEndRequirementsList.php";s:4:"7560";s:52:"pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php";s:4:"1335";s:42:"pi1/class.tx_seminars_pi1_frontEndView.php";s:4:"0ba9";s:48:"pi1/class.tx_seminars_pi1_registrationEditor.php";s:4:"3dd2";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"2086";s:20:"pi1/event_editor.xml";s:4:"8d7a";s:17:"pi1/locallang.xml";s:4:"5d47";s:28:"pi1/registration_editor.html";s:4:"f4d7";s:33:"pi1/registration_editor_step1.xml";s:4:"df91";s:33:"pi1/registration_editor_step2.xml";s:4:"bd21";s:42:"pi1/registration_editor_unregistration.xml";s:4:"a7d2";s:20:"pi1/seminars_pi1.css";s:4:"6b93";s:21:"pi1/seminars_pi1.tmpl";s:4:"f191";s:22:"pi1/tx_seminars_pi1.js";s:4:"cab2";}',
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