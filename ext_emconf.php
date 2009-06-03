<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 03-06-2009 21:04
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
	'conflicts' => 'dbal,date2cal',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1,BackEnd',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'fe_groups,tx_partner_contact_info,tx_partner_main',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.8.52',
	'_md5_values_when_last_written' => 'a:150:{s:13:"changelog.txt";s:4:"99b2";s:20:"class.ext_update.php";s:4:"9851";s:41:"class.tx_seminars_OrganizerBagBuilder.php";s:4:"6b51";s:25:"class.tx_seminars_bag.php";s:4:"6d58";s:32:"class.tx_seminars_bagbuilder.php";s:4:"4404";s:30:"class.tx_seminars_category.php";s:4:"b216";s:33:"class.tx_seminars_categorybag.php";s:4:"6330";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"6799";s:33:"class.tx_seminars_configcheck.php";s:4:"983c";s:34:"class.tx_seminars_configgetter.php";s:4:"8205";s:31:"class.tx_seminars_flexForms.php";s:4:"6ab2";s:34:"class.tx_seminars_objectfromdb.php";s:4:"698d";s:31:"class.tx_seminars_organizer.php";s:4:"fd95";s:34:"class.tx_seminars_organizerbag.php";s:4:"be65";s:27:"class.tx_seminars_place.php";s:4:"80d4";s:30:"class.tx_seminars_placebag.php";s:4:"8744";s:34:"class.tx_seminars_registration.php";s:4:"e94f";s:44:"class.tx_seminars_registrationBagBuilder.php";s:4:"0d32";s:37:"class.tx_seminars_registrationbag.php";s:4:"6d3e";s:41:"class.tx_seminars_registrationmanager.php";s:4:"fee7";s:29:"class.tx_seminars_seminar.php";s:4:"3558";s:32:"class.tx_seminars_seminarbag.php";s:4:"b398";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"63b9";s:29:"class.tx_seminars_speaker.php";s:4:"6193";s:32:"class.tx_seminars_speakerbag.php";s:4:"163f";s:29:"class.tx_seminars_tcemain.php";s:4:"8b9b";s:30:"class.tx_seminars_timeslot.php";s:4:"e9da";s:33:"class.tx_seminars_timeslotbag.php";s:4:"bea9";s:30:"class.tx_seminars_timespan.php";s:4:"3a8a";s:21:"ext_conf_template.txt";s:4:"edef";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"3c25";s:14:"ext_tables.php";s:4:"16cd";s:14:"ext_tables.sql";s:4:"be77";s:13:"locallang.xml";s:4:"f3b7";s:16:"locallang_db.xml";s:4:"a79e";s:8:"todo.txt";s:4:"3bc2";s:53:"Resources/Private/Language/locallang_csh_seminars.xml";s:4:"a3cc";s:44:"Resources/Private/Templates/Mail/e-mail.html";s:4:"5e24";s:38:"Resources/Private/CSS/thankYouMail.css";s:4:"4e2b";s:19:"BackEnd/BackEnd.css";s:4:"cee5";s:41:"BackEnd/class.tx_seminars_BackEnd_CSV.php";s:4:"0185";s:57:"BackEnd/class.tx_seminars_BackEnd_CancelEventMailForm.php";s:4:"3b63";s:58:"BackEnd/class.tx_seminars_BackEnd_ConfirmEventMailForm.php";s:4:"b83f";s:51:"BackEnd/class.tx_seminars_BackEnd_EventMailForm.php";s:4:"8d87";s:48:"BackEnd/class.tx_seminars_BackEnd_EventsList.php";s:4:"a815";s:42:"BackEnd/class.tx_seminars_BackEnd_List.php";s:4:"62dd";s:44:"BackEnd/class.tx_seminars_BackEnd_Module.php";s:4:"f040";s:52:"BackEnd/class.tx_seminars_BackEnd_OrganizersList.php";s:4:"084e";s:55:"BackEnd/class.tx_seminars_BackEnd_RegistrationsList.php";s:4:"e4da";s:50:"BackEnd/class.tx_seminars_BackEnd_SpeakersList.php";s:4:"9024";s:16:"BackEnd/conf.php";s:4:"7ce7";s:25:"BackEnd/icon_canceled.png";s:4:"4161";s:26:"BackEnd/icon_confirmed.png";s:4:"77af";s:17:"BackEnd/index.php";s:4:"27b1";s:21:"BackEnd/locallang.xml";s:4:"c568";s:25:"BackEnd/locallang_mod.xml";s:4:"0cdd";s:22:"BackEnd/moduleicon.gif";s:4:"032e";s:38:"Configuration/TypoScript/constants.txt";s:4:"c133";s:34:"Configuration/TypoScript/setup.txt";s:4:"f51b";s:25:"Configuration/TCA/tca.php";s:4:"b510";s:41:"Configuration/FlexForms/flexforms_pi1.xml";s:4:"e5e6";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1218";s:14:"mod1/index.php";s:4:"7f78";s:18:"mod1/locallang.xml";s:4:"c867";s:22:"mod1/locallang_mod.xml";s:4:"2d24";s:19:"mod1/moduleicon.gif";s:4:"8074";s:29:"lib/tx_seminars_constants.php";s:4:"df50";s:44:"Mapper/class.tx_seminars_Mapper_Category.php";s:4:"791d";s:44:"Mapper/class.tx_seminars_Mapper_Checkbox.php";s:4:"c7f1";s:41:"Mapper/class.tx_seminars_Mapper_Event.php";s:4:"7e25";s:45:"Mapper/class.tx_seminars_Mapper_EventType.php";s:4:"cc4c";s:40:"Mapper/class.tx_seminars_Mapper_Food.php";s:4:"e1a9";s:48:"Mapper/class.tx_seminars_Mapper_FrontEndUser.php";s:4:"d69c";s:53:"Mapper/class.tx_seminars_Mapper_FrontEndUserGroup.php";s:4:"6b52";s:43:"Mapper/class.tx_seminars_Mapper_Lodging.php";s:4:"c347";s:45:"Mapper/class.tx_seminars_Mapper_Organizer.php";s:4:"eab8";s:49:"Mapper/class.tx_seminars_Mapper_PaymentMethod.php";s:4:"c527";s:41:"Mapper/class.tx_seminars_Mapper_Place.php";s:4:"b82c";s:41:"Mapper/class.tx_seminars_Mapper_Skill.php";s:4:"1ead";s:43:"Mapper/class.tx_seminars_Mapper_Speaker.php";s:4:"d11b";s:47:"Mapper/class.tx_seminars_Mapper_TargetGroup.php";s:4:"d390";s:44:"Mapper/class.tx_seminars_Mapper_TimeSlot.php";s:4:"479e";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:33:"icons/icon_tx_seminars_prices.gif";s:4:"61a5";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"bd58";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"c6a7";s:17:"pi2/locallang.xml";s:4:"ef40";s:50:"Model/class.tx_seminars_Model_AbstractTimeSpan.php";s:4:"b95e";s:42:"Model/class.tx_seminars_Model_Category.php";s:4:"d336";s:42:"Model/class.tx_seminars_Model_Checkbox.php";s:4:"70a4";s:39:"Model/class.tx_seminars_Model_Event.php";s:4:"fc98";s:43:"Model/class.tx_seminars_Model_EventType.php";s:4:"8eb5";s:38:"Model/class.tx_seminars_Model_Food.php";s:4:"28fa";s:46:"Model/class.tx_seminars_Model_FrontEndUser.php";s:4:"b4bf";s:51:"Model/class.tx_seminars_Model_FrontEndUserGroup.php";s:4:"25a1";s:41:"Model/class.tx_seminars_Model_Lodging.php";s:4:"07b3";s:43:"Model/class.tx_seminars_Model_Organizer.php";s:4:"47eb";s:47:"Model/class.tx_seminars_Model_PaymentMethod.php";s:4:"e27d";s:39:"Model/class.tx_seminars_Model_Place.php";s:4:"a602";s:39:"Model/class.tx_seminars_Model_Skill.php";s:4:"a4db";s:41:"Model/class.tx_seminars_Model_Speaker.php";s:4:"ec09";s:45:"Model/class.tx_seminars_Model_TargetGroup.php";s:4:"2217";s:42:"Model/class.tx_seminars_Model_TimeSlot.php";s:4:"2410";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"12f1";s:14:"doc/manual.sxw";s:4:"7635";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"4a32";s:41:"pi1/class.tx_seminars_pi1_eventEditor.php";s:4:"312e";s:50:"pi1/class.tx_seminars_pi1_frontEndCategoryList.php";s:4:"9706";s:47:"pi1/class.tx_seminars_pi1_frontEndCountdown.php";s:4:"408b";s:44:"pi1/class.tx_seminars_pi1_frontEndEditor.php";s:4:"9c85";s:51:"pi1/class.tx_seminars_pi1_frontEndEventHeadline.php";s:4:"8fdc";s:55:"pi1/class.tx_seminars_pi1_frontEndRegistrationsList.php";s:4:"e597";s:54:"pi1/class.tx_seminars_pi1_frontEndRequirementsList.php";s:4:"871b";s:52:"pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php";s:4:"7629";s:42:"pi1/class.tx_seminars_pi1_frontEndView.php";s:4:"0ba9";s:48:"pi1/class.tx_seminars_pi1_registrationEditor.php";s:4:"421a";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"6dea";s:17:"pi1/locallang.xml";s:4:"4434";s:28:"pi1/registration_editor.html";s:4:"57e2";s:20:"pi1/seminars_pi1.css";s:4:"6ea1";s:21:"pi1/seminars_pi1.tmpl";s:4:"a55a";s:22:"pi1/tx_seminars_pi1.js";s:4:"cab2";s:42:"cli/class.tx_seminars_cli_MailNotifier.php";s:4:"d74e";s:23:"cli/tx_seminars_cli.php";s:4:"3dca";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.2.0-0.0.0',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.5.2-',
			'ameos_formidable' => '1.1.0-1.9.99',
			'partner' => '0.5.0-',
			'static_info_tables' => '2.0.8-',
			'static_info_tables_taxes' => '',
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