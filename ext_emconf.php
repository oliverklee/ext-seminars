<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "seminars".
 *
 * Auto generated 22-01-2014 01:23
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Seminar Manager',
	'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,css_styled_content,oelib,ameos_formidable,static_info_tables,static_info_tables_taxes',
	'conflicts' => 'dbal,sourceopt',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'BackEnd,BackEndExtJs',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'be_groups,fe_groups,fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.9.84',
	'_md5_values_when_last_written' => 'a:181:{s:13:"changelog.txt";s:4:"132e";s:20:"class.ext_update.php";s:4:"55ac";s:33:"class.tx_seminars_configcheck.php";s:4:"5301";s:34:"class.tx_seminars_configgetter.php";s:4:"3189";s:37:"class.tx_seminars_EmailSalutation.php";s:4:"8c64";s:31:"class.tx_seminars_flexForms.php";s:4:"0f1d";s:34:"class.tx_seminars_registration.php";s:4:"5e50";s:41:"class.tx_seminars_registrationmanager.php";s:4:"90f5";s:29:"class.tx_seminars_seminar.php";s:4:"81d2";s:29:"class.tx_seminars_speaker.php";s:4:"320f";s:29:"class.tx_seminars_tcemain.php";s:4:"b8c0";s:30:"class.tx_seminars_timeslot.php";s:4:"fe6e";s:30:"class.tx_seminars_timespan.php";s:4:"207a";s:16:"ext_autoload.php";s:4:"5255";s:21:"ext_conf_template.txt";s:4:"a043";s:12:"ext_icon.gif";s:4:"35fc";s:17:"ext_localconf.php";s:4:"4278";s:14:"ext_tables.php";s:4:"22a0";s:14:"ext_tables.sql";s:4:"57cf";s:13:"locallang.xml";s:4:"0f11";s:16:"locallang_db.xml";s:4:"7b31";s:36:"tx_seminars_modifiedSystemTables.php";s:4:"fa13";s:33:"BackEnd/AbstractEventMailForm.php";s:4:"2483";s:24:"BackEnd/AbstractList.php";s:4:"afb8";s:19:"BackEnd/BackEnd.css";s:4:"b59c";s:31:"BackEnd/CancelEventMailForm.php";s:4:"d9a4";s:16:"BackEnd/conf.php";s:4:"7746";s:32:"BackEnd/ConfirmEventMailForm.php";s:4:"6630";s:15:"BackEnd/CSV.php";s:4:"d41d";s:22:"BackEnd/EventsList.php";s:4:"ffb8";s:32:"BackEnd/GeneralEventMailForm.php";s:4:"369d";s:17:"BackEnd/index.php";s:4:"d355";s:21:"BackEnd/locallang.xml";s:4:"298f";s:25:"BackEnd/locallang_mod.xml";s:4:"0cdd";s:18:"BackEnd/Module.php";s:4:"eece";s:22:"BackEnd/moduleicon.gif";s:4:"032e";s:26:"BackEnd/OrganizersList.php";s:4:"c4b5";s:29:"BackEnd/RegistrationsList.php";s:4:"dc56";s:24:"BackEnd/SpeakersList.php";s:4:"1133";s:16:"Bag/Abstract.php";s:4:"a365";s:16:"Bag/Category.php";s:4:"2002";s:13:"Bag/Event.php";s:4:"3168";s:17:"Bag/Organizer.php";s:4:"a501";s:20:"Bag/Registration.php";s:4:"0433";s:15:"Bag/Speaker.php";s:4:"ed5d";s:16:"Bag/TimeSlot.php";s:4:"d577";s:23:"BagBuilder/Abstract.php";s:4:"090f";s:23:"BagBuilder/Category.php";s:4:"ba08";s:20:"BagBuilder/Event.php";s:4:"1f18";s:24:"BagBuilder/Organizer.php";s:4:"f448";s:27:"BagBuilder/Registration.php";s:4:"289b";s:22:"BagBuilder/Speaker.php";s:4:"5e5a";s:41:"Configuration/FlexForms/flexforms_pi1.xml";s:4:"a724";s:25:"Configuration/TCA/tca.php";s:4:"6f21";s:38:"Configuration/TypoScript/constants.txt";s:4:"8464";s:34:"Configuration/TypoScript/setup.txt";s:4:"fada";s:34:"Csv/AbstractBackEndAccessCheck.php";s:4:"2049";s:24:"Csv/AbstractListView.php";s:4:"37eb";s:36:"Csv/AbstractRegistrationListView.php";s:4:"65c0";s:31:"Csv/BackEndEventAccessCheck.php";s:4:"a4ed";s:38:"Csv/BackEndRegistrationAccessCheck.php";s:4:"3172";s:36:"Csv/DownloadRegistrationListView.php";s:4:"cfd2";s:33:"Csv/EmailRegistrationListView.php";s:4:"110c";s:21:"Csv/EventListView.php";s:4:"29a5";s:39:"Csv/FrontEndRegistrationAccessCheck.php";s:4:"2e3d";s:25:"FrontEnd/AbstractView.php";s:4:"1160";s:25:"FrontEnd/CategoryList.php";s:4:"c15f";s:22:"FrontEnd/Countdown.php";s:4:"2c44";s:30:"FrontEnd/DefaultController.php";s:4:"0f50";s:19:"FrontEnd/Editor.php";s:4:"5017";s:24:"FrontEnd/EventEditor.php";s:4:"187f";s:26:"FrontEnd/EventHeadline.php";s:4:"8958";s:25:"FrontEnd/PublishEvent.php";s:4:"e721";s:29:"FrontEnd/RegistrationForm.php";s:4:"80a7";s:30:"FrontEnd/RegistrationsList.php";s:4:"5a17";s:29:"FrontEnd/RequirementsList.php";s:4:"2d38";s:27:"FrontEnd/SelectorWidget.php";s:4:"35c2";s:23:"FrontEnd/WizardIcon.php";s:4:"a835";s:28:"Interface/CsvAccessCheck.php";s:4:"ea62";s:20:"Interface/Titled.php";s:4:"7a16";s:32:"Interface/Hook/BackEndModule.php";s:4:"8447";s:32:"Interface/Hook/EventListView.php";s:4:"f6d1";s:34:"Interface/Hook/EventSingleView.php";s:4:"4393";s:31:"Interface/Hook/Registration.php";s:4:"ec9a";s:22:"Mapper/BackEndUser.php";s:4:"858b";s:27:"Mapper/BackEndUserGroup.php";s:4:"54dc";s:19:"Mapper/Category.php";s:4:"4ba2";s:19:"Mapper/Checkbox.php";s:4:"1f47";s:16:"Mapper/Event.php";s:4:"7ea0";s:20:"Mapper/EventType.php";s:4:"fe3c";s:15:"Mapper/Food.php";s:4:"be25";s:23:"Mapper/FrontEndUser.php";s:4:"a28b";s:28:"Mapper/FrontEndUserGroup.php";s:4:"3561";s:18:"Mapper/Lodging.php";s:4:"dd82";s:20:"Mapper/Organizer.php";s:4:"000b";s:24:"Mapper/PaymentMethod.php";s:4:"7eb4";s:16:"Mapper/Place.php";s:4:"9f99";s:23:"Mapper/Registration.php";s:4:"987e";s:16:"Mapper/Skill.php";s:4:"86cc";s:18:"Mapper/Speaker.php";s:4:"52f5";s:22:"Mapper/TargetGroup.php";s:4:"6b46";s:19:"Mapper/TimeSlot.php";s:4:"fe62";s:26:"Model/AbstractTimeSpan.php";s:4:"1794";s:21:"Model/BackEndUser.php";s:4:"f9ae";s:26:"Model/BackEndUserGroup.php";s:4:"9bdf";s:18:"Model/Category.php";s:4:"3b02";s:18:"Model/Checkbox.php";s:4:"8416";s:15:"Model/Event.php";s:4:"721f";s:19:"Model/EventType.php";s:4:"d28b";s:14:"Model/Food.php";s:4:"c361";s:22:"Model/FrontEndUser.php";s:4:"1787";s:27:"Model/FrontEndUserGroup.php";s:4:"9fb1";s:17:"Model/Lodging.php";s:4:"5ece";s:19:"Model/Organizer.php";s:4:"84ad";s:23:"Model/PaymentMethod.php";s:4:"adba";s:15:"Model/Place.php";s:4:"9de7";s:22:"Model/Registration.php";s:4:"20fa";s:15:"Model/Skill.php";s:4:"fbcd";s:17:"Model/Speaker.php";s:4:"2a80";s:21:"Model/TargetGroup.php";s:4:"621d";s:18:"Model/TimeSlot.php";s:4:"7a80";s:21:"OldModel/Abstract.php";s:4:"ece3";s:21:"OldModel/Category.php";s:4:"5e0d";s:22:"OldModel/Organizer.php";s:4:"d56f";s:38:"Resources/Private/CSS/thankYouMail.css";s:4:"4e2b";s:40:"Resources/Private/Language/locallang.xml";s:4:"909d";s:54:"Resources/Private/Language/locallang_csh_fe_groups.xml";s:4:"2c9e";s:53:"Resources/Private/Language/locallang_csh_seminars.xml";s:4:"f521";s:49:"Resources/Private/Language/FrontEnd/locallang.xml";s:4:"a931";s:51:"Resources/Private/Templates/BackEnd/EventsList.html";s:4:"41ea";s:55:"Resources/Private/Templates/BackEnd/OrganizersList.html";s:4:"abb9";s:58:"Resources/Private/Templates/BackEnd/RegistrationsList.html";s:4:"c80e";s:53:"Resources/Private/Templates/BackEnd/SpeakersList.html";s:4:"cf8a";s:53:"Resources/Private/Templates/FrontEnd/EventEditor.html";s:4:"4498";s:50:"Resources/Private/Templates/FrontEnd/FrontEnd.html";s:4:"4f4b";s:60:"Resources/Private/Templates/FrontEnd/RegistrationEditor.html";s:4:"2a53";s:44:"Resources/Private/Templates/Mail/e-mail.html";s:4:"619c";s:38:"Resources/Public/CSS/BackEnd/Print.css";s:4:"d41d";s:42:"Resources/Public/CSS/FrontEnd/FrontEnd.css";s:4:"bf1f";s:35:"Resources/Public/Icons/Canceled.png";s:4:"4161";s:35:"Resources/Public/Icons/Category.gif";s:4:"c95b";s:35:"Resources/Public/Icons/Checkbox.gif";s:4:"f1f0";s:36:"Resources/Public/Icons/Confirmed.png";s:4:"77af";s:40:"Resources/Public/Icons/ContentWizard.gif";s:4:"5e60";s:40:"Resources/Public/Icons/EventComplete.gif";s:4:"d4db";s:43:"Resources/Public/Icons/EventComplete__h.gif";s:4:"ccf3";s:43:"Resources/Public/Icons/EventComplete__t.gif";s:4:"a5cc";s:36:"Resources/Public/Icons/EventDate.gif";s:4:"7853";s:39:"Resources/Public/Icons/EventDate__h.gif";s:4:"fd86";s:39:"Resources/Public/Icons/EventDate__t.gif";s:4:"acc7";s:37:"Resources/Public/Icons/EventTopic.gif";s:4:"e4b1";s:40:"Resources/Public/Icons/EventTopic__h.gif";s:4:"4689";s:40:"Resources/Public/Icons/EventTopic__t.gif";s:4:"e220";s:36:"Resources/Public/Icons/EventType.gif";s:4:"61a5";s:31:"Resources/Public/Icons/Food.gif";s:4:"1024";s:34:"Resources/Public/Icons/Lodging.gif";s:4:"5fdf";s:36:"Resources/Public/Icons/Organizer.gif";s:4:"1e7e";s:40:"Resources/Public/Icons/PaymentMethod.gif";s:4:"44bd";s:32:"Resources/Public/Icons/Place.gif";s:4:"2694";s:32:"Resources/Public/Icons/Price.gif";s:4:"61a5";s:32:"Resources/Public/Icons/Print.png";s:4:"2424";s:39:"Resources/Public/Icons/Registration.gif";s:4:"d892";s:42:"Resources/Public/Icons/Registration__h.gif";s:4:"5571";s:32:"Resources/Public/Icons/Skill.gif";s:4:"30a2";s:34:"Resources/Public/Icons/Speaker.gif";s:4:"ddc1";s:38:"Resources/Public/Icons/TargetGroup.gif";s:4:"b5a7";s:31:"Resources/Public/Icons/Test.gif";s:4:"bd58";s:35:"Resources/Public/Icons/TimeSlot.gif";s:4:"bb73";s:48:"Resources/Public/JavaScript/FrontEnd/FrontEnd.js";s:4:"8d5f";s:33:"Service/SingleViewLinkBuilder.php";s:4:"9835";s:35:"ViewHelper/CommaSeparatedTitles.php";s:4:"127b";s:24:"ViewHelper/Countdown.php";s:4:"2951";s:24:"ViewHelper/DateRange.php";s:4:"07eb";s:24:"ViewHelper/TimeRange.php";s:4:"581d";s:42:"cli/class.tx_seminars_cli_MailNotifier.php";s:4:"5968";s:23:"cli/tx_seminars_cli.php";s:4:"f4ef";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"bac4";s:14:"doc/manual.sxw";s:4:"d7a2";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"b5a3";s:17:"pi2/locallang.xml";s:4:"ef40";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '4.5.0-4.7.99',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.7.67-',
			'ameos_formidable' => '1.1.0-1.9.99',
			'static_info_tables' => '2.1.0-',
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
	),
);