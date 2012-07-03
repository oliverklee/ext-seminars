<?php

########################################################################
# Extension Manager/Repository config file for ext "seminars".
#
# Auto generated 03-07-2012 13:06
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Seminar Manager',
	'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,css_styled_content,oelib,ameos_formidable,static_info_tables,static_info_tables_taxes',
	'conflicts' => 'dbal,sourceopt',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1,BackEnd',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'be_groups,fe_groups',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.9.4',
	'_md5_values_when_last_written' => 'a:170:{s:13:"changelog.txt";s:4:"7646";s:20:"class.ext_update.php";s:4:"b791";s:37:"class.tx_seminars_EmailSalutation.php";s:4:"320e";s:41:"class.tx_seminars_OrganizerBagBuilder.php";s:4:"a326";s:39:"class.tx_seminars_SpeakerBagBuilder.php";s:4:"178b";s:25:"class.tx_seminars_bag.php";s:4:"6fc1";s:32:"class.tx_seminars_bagbuilder.php";s:4:"8f83";s:30:"class.tx_seminars_category.php";s:4:"0cb1";s:33:"class.tx_seminars_categorybag.php";s:4:"676f";s:40:"class.tx_seminars_categorybagbuilder.php";s:4:"de97";s:33:"class.tx_seminars_configcheck.php";s:4:"91ce";s:34:"class.tx_seminars_configgetter.php";s:4:"2fb0";s:31:"class.tx_seminars_flexForms.php";s:4:"f53d";s:34:"class.tx_seminars_objectfromdb.php";s:4:"6c77";s:31:"class.tx_seminars_organizer.php";s:4:"ffe5";s:34:"class.tx_seminars_organizerbag.php";s:4:"f117";s:27:"class.tx_seminars_place.php";s:4:"309b";s:30:"class.tx_seminars_placebag.php";s:4:"9b22";s:34:"class.tx_seminars_registration.php";s:4:"292b";s:44:"class.tx_seminars_registrationBagBuilder.php";s:4:"4c67";s:37:"class.tx_seminars_registrationbag.php";s:4:"e9e0";s:41:"class.tx_seminars_registrationmanager.php";s:4:"d410";s:29:"class.tx_seminars_seminar.php";s:4:"f8ff";s:32:"class.tx_seminars_seminarbag.php";s:4:"66d0";s:39:"class.tx_seminars_seminarbagbuilder.php";s:4:"8ac5";s:29:"class.tx_seminars_speaker.php";s:4:"7e7c";s:32:"class.tx_seminars_speakerbag.php";s:4:"220b";s:29:"class.tx_seminars_tcemain.php";s:4:"5875";s:30:"class.tx_seminars_timeslot.php";s:4:"2169";s:33:"class.tx_seminars_timeslotbag.php";s:4:"e909";s:30:"class.tx_seminars_timespan.php";s:4:"de72";s:16:"ext_autoload.php";s:4:"bddc";s:21:"ext_conf_template.txt";s:4:"9349";s:12:"ext_icon.gif";s:4:"35fc";s:17:"ext_localconf.php";s:4:"3c25";s:14:"ext_tables.php";s:4:"9030";s:14:"ext_tables.sql";s:4:"e627";s:13:"locallang.xml";s:4:"0f11";s:16:"locallang_db.xml";s:4:"e542";s:8:"todo.txt";s:4:"9f3f";s:36:"tx_seminars_modifiedSystemTables.php";s:4:"410b";s:19:"BackEnd/BackEnd.css";s:4:"f6bd";s:41:"BackEnd/class.tx_seminars_BackEnd_CSV.php";s:4:"2fa2";s:57:"BackEnd/class.tx_seminars_BackEnd_CancelEventMailForm.php";s:4:"65f9";s:58:"BackEnd/class.tx_seminars_BackEnd_ConfirmEventMailForm.php";s:4:"d985";s:51:"BackEnd/class.tx_seminars_BackEnd_EventMailForm.php";s:4:"653e";s:48:"BackEnd/class.tx_seminars_BackEnd_EventsList.php";s:4:"4511";s:42:"BackEnd/class.tx_seminars_BackEnd_List.php";s:4:"fac9";s:44:"BackEnd/class.tx_seminars_BackEnd_Module.php";s:4:"2b30";s:52:"BackEnd/class.tx_seminars_BackEnd_OrganizersList.php";s:4:"ce72";s:55:"BackEnd/class.tx_seminars_BackEnd_RegistrationsList.php";s:4:"745b";s:50:"BackEnd/class.tx_seminars_BackEnd_SpeakersList.php";s:4:"a3b4";s:16:"BackEnd/conf.php";s:4:"7ce7";s:25:"BackEnd/icon_canceled.png";s:4:"4161";s:26:"BackEnd/icon_confirmed.png";s:4:"77af";s:17:"BackEnd/index.php";s:4:"ee75";s:21:"BackEnd/locallang.xml";s:4:"651c";s:25:"BackEnd/locallang_mod.xml";s:4:"0cdd";s:22:"BackEnd/moduleicon.gif";s:4:"032e";s:41:"Configuration/FlexForms/flexforms_pi1.xml";s:4:"f156";s:25:"Configuration/TCA/tca.php";s:4:"c791";s:38:"Configuration/TypoScript/constants.txt";s:4:"2a54";s:34:"Configuration/TypoScript/setup.txt";s:4:"d0a4";s:32:"Interface/Hook/EventListView.php";s:4:"8c83";s:34:"Interface/Hook/EventSingleView.php";s:4:"8a83";s:47:"Mapper/class.tx_seminars_Mapper_BackEndUser.php";s:4:"db33";s:52:"Mapper/class.tx_seminars_Mapper_BackEndUserGroup.php";s:4:"3830";s:44:"Mapper/class.tx_seminars_Mapper_Category.php";s:4:"ddb4";s:44:"Mapper/class.tx_seminars_Mapper_Checkbox.php";s:4:"767c";s:41:"Mapper/class.tx_seminars_Mapper_Event.php";s:4:"a68d";s:45:"Mapper/class.tx_seminars_Mapper_EventType.php";s:4:"264c";s:40:"Mapper/class.tx_seminars_Mapper_Food.php";s:4:"50b3";s:48:"Mapper/class.tx_seminars_Mapper_FrontEndUser.php";s:4:"27b8";s:53:"Mapper/class.tx_seminars_Mapper_FrontEndUserGroup.php";s:4:"0ca3";s:43:"Mapper/class.tx_seminars_Mapper_Lodging.php";s:4:"5af8";s:45:"Mapper/class.tx_seminars_Mapper_Organizer.php";s:4:"b6ba";s:49:"Mapper/class.tx_seminars_Mapper_PaymentMethod.php";s:4:"f5a1";s:41:"Mapper/class.tx_seminars_Mapper_Place.php";s:4:"cfb3";s:48:"Mapper/class.tx_seminars_Mapper_Registration.php";s:4:"5c0c";s:41:"Mapper/class.tx_seminars_Mapper_Skill.php";s:4:"beb5";s:43:"Mapper/class.tx_seminars_Mapper_Speaker.php";s:4:"6be9";s:47:"Mapper/class.tx_seminars_Mapper_TargetGroup.php";s:4:"f39f";s:44:"Mapper/class.tx_seminars_Mapper_TimeSlot.php";s:4:"ebbe";s:50:"Model/class.tx_seminars_Model_AbstractTimeSpan.php";s:4:"3704";s:45:"Model/class.tx_seminars_Model_BackEndUser.php";s:4:"12d5";s:50:"Model/class.tx_seminars_Model_BackEndUserGroup.php";s:4:"d64f";s:42:"Model/class.tx_seminars_Model_Category.php";s:4:"5faf";s:42:"Model/class.tx_seminars_Model_Checkbox.php";s:4:"59b3";s:39:"Model/class.tx_seminars_Model_Event.php";s:4:"ce3e";s:43:"Model/class.tx_seminars_Model_EventType.php";s:4:"c2d8";s:38:"Model/class.tx_seminars_Model_Food.php";s:4:"32af";s:46:"Model/class.tx_seminars_Model_FrontEndUser.php";s:4:"f114";s:51:"Model/class.tx_seminars_Model_FrontEndUserGroup.php";s:4:"bf20";s:41:"Model/class.tx_seminars_Model_Lodging.php";s:4:"bd2f";s:43:"Model/class.tx_seminars_Model_Organizer.php";s:4:"afce";s:47:"Model/class.tx_seminars_Model_PaymentMethod.php";s:4:"c2f8";s:39:"Model/class.tx_seminars_Model_Place.php";s:4:"0df5";s:46:"Model/class.tx_seminars_Model_Registration.php";s:4:"511c";s:39:"Model/class.tx_seminars_Model_Skill.php";s:4:"d590";s:41:"Model/class.tx_seminars_Model_Speaker.php";s:4:"3b3e";s:45:"Model/class.tx_seminars_Model_TargetGroup.php";s:4:"b7ab";s:42:"Model/class.tx_seminars_Model_TimeSlot.php";s:4:"622d";s:38:"Resources/Private/CSS/thankYouMail.css";s:4:"4e2b";s:40:"Resources/Private/Language/locallang.xml";s:4:"909d";s:54:"Resources/Private/Language/locallang_csh_fe_groups.xml";s:4:"2c9e";s:53:"Resources/Private/Language/locallang_csh_seminars.xml";s:4:"f521";s:51:"Resources/Private/Templates/BackEnd/EventsList.html";s:4:"8d1a";s:55:"Resources/Private/Templates/BackEnd/OrganizersList.html";s:4:"81a3";s:58:"Resources/Private/Templates/BackEnd/RegistrationsList.html";s:4:"f7cd";s:53:"Resources/Private/Templates/BackEnd/SpeakersList.html";s:4:"a171";s:53:"Resources/Private/Templates/FrontEnd/EventEditor.html";s:4:"4498";s:60:"Resources/Private/Templates/FrontEnd/RegistrationEditor.html";s:4:"1ae4";s:44:"Resources/Private/Templates/Mail/e-mail.html";s:4:"619c";s:42:"cli/class.tx_seminars_cli_MailNotifier.php";s:4:"9702";s:23:"cli/tx_seminars_cli.php";s:4:"965e";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"4b1b";s:14:"doc/manual.sxw";s:4:"159e";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:41:"icons/icon_tx_seminars_attendances__h.gif";s:4:"5571";s:37:"icons/icon_tx_seminars_categories.gif";s:4:"c95b";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:33:"icons/icon_tx_seminars_prices.gif";s:4:"61a5";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"7853";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"fd86";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"acc7";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:33:"icons/icon_tx_seminars_skills.gif";s:4:"30a2";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:40:"icons/icon_tx_seminars_target_groups.gif";s:4:"b5a7";s:31:"icons/icon_tx_seminars_test.gif";s:4:"bd58";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"lib/tx_seminars_constants.php";s:4:"e400";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1218";s:14:"mod1/index.php";s:4:"52ca";s:18:"mod1/locallang.xml";s:4:"c867";s:22:"mod1/locallang_mod.xml";s:4:"2d24";s:19:"mod1/moduleicon.gif";s:4:"8074";s:14:"pi1/ce_wiz.gif";s:4:"5e60";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"9b60";s:41:"pi1/class.tx_seminars_pi1_eventEditor.php";s:4:"9e9c";s:50:"pi1/class.tx_seminars_pi1_frontEndCategoryList.php";s:4:"ae2b";s:47:"pi1/class.tx_seminars_pi1_frontEndCountdown.php";s:4:"e7b1";s:44:"pi1/class.tx_seminars_pi1_frontEndEditor.php";s:4:"5050";s:51:"pi1/class.tx_seminars_pi1_frontEndEventHeadline.php";s:4:"d124";s:50:"pi1/class.tx_seminars_pi1_frontEndPublishEvent.php";s:4:"aa89";s:55:"pi1/class.tx_seminars_pi1_frontEndRegistrationsList.php";s:4:"a7c8";s:54:"pi1/class.tx_seminars_pi1_frontEndRequirementsList.php";s:4:"ebc4";s:52:"pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php";s:4:"3cb7";s:42:"pi1/class.tx_seminars_pi1_frontEndView.php";s:4:"0f6f";s:48:"pi1/class.tx_seminars_pi1_registrationEditor.php";s:4:"fd29";s:37:"pi1/class.tx_seminars_pi1_wizicon.php";s:4:"0d57";s:17:"pi1/locallang.xml";s:4:"dbc5";s:20:"pi1/seminars_pi1.css";s:4:"37df";s:21:"pi1/seminars_pi1.tmpl";s:4:"9a8a";s:22:"pi1/tx_seminars_pi1.js";s:4:"ce0e";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"883a";s:17:"pi2/locallang.xml";s:4:"ef40";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.2.0-0.0.0',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.7.0-',
			'ameos_formidable' => '1.1.0-1.9.99',
			'static_info_tables' => '2.0.8-',
			'static_info_tables_taxes' => '',
		),
		'conflicts' => array(
			'dbal' => '',
			'sourceopt' => '',
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