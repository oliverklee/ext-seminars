<?php

########################################################################
# Extension Manager/Repository config file for ext: "seminars"
#
# Auto generated 29-06-2007 18:15
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
	'version' => '0.5.0',
	'_md5_values_when_last_written' => 'a:88:{s:13:"changelog.txt";s:4:"eec9";s:25:"class.tx_seminars_bag.php";s:4:"73d9";s:33:"class.tx_seminars_configcheck.php";s:4:"16e0";s:34:"class.tx_seminars_configgetter.php";s:4:"ea15";s:30:"class.tx_seminars_dbplugin.php";s:4:"62ff";s:34:"class.tx_seminars_objectfromdb.php";s:4:"cabd";s:36:"class.tx_seminars_oe_configcheck.php";s:4:"6223";s:31:"class.tx_seminars_organizer.php";s:4:"65ae";s:34:"class.tx_seminars_organizerbag.php";s:4:"60be";s:27:"class.tx_seminars_place.php";s:4:"b302";s:30:"class.tx_seminars_placebag.php";s:4:"3fe5";s:34:"class.tx_seminars_registration.php";s:4:"a6cd";s:37:"class.tx_seminars_registrationbag.php";s:4:"3a0e";s:41:"class.tx_seminars_registrationmanager.php";s:4:"5ec6";s:40:"class.tx_seminars_salutationswitcher.php";s:4:"c6fc";s:29:"class.tx_seminars_seminar.php";s:4:"bd1c";s:32:"class.tx_seminars_seminarbag.php";s:4:"24ce";s:29:"class.tx_seminars_speaker.php";s:4:"0a62";s:32:"class.tx_seminars_speakerbag.php";s:4:"e535";s:29:"class.tx_seminars_tcemain.php";s:4:"00cc";s:36:"class.tx_seminars_templatehelper.php";s:4:"bdd8";s:30:"class.tx_seminars_timeslot.php";s:4:"0274";s:30:"class.tx_seminars_timespan.php";s:4:"6673";s:21:"ext_conf_template.txt";s:4:"8b8a";s:12:"ext_icon.gif";s:4:"032e";s:17:"ext_localconf.php";s:4:"2297";s:14:"ext_tables.php";s:4:"d3ef";s:14:"ext_tables.sql";s:4:"28a5";s:19:"flexform_pi1_ds.xml";s:4:"22f6";s:13:"locallang.xml";s:4:"3543";s:16:"locallang_db.xml";s:4:"6cfe";s:13:"seminars.tmpl";s:4:"ed87";s:7:"tca.php";s:4:"a46b";s:8:"todo.txt";s:4:"70e2";s:25:"api/class.tx_seminars.php";s:4:"df38";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"1137";s:14:"mod1/index.php";s:4:"cb2f";s:18:"mod1/locallang.xml";s:4:"33d2";s:22:"mod1/locallang_mod.xml";s:4:"68ac";s:19:"mod1/moduleicon.gif";s:4:"8074";s:38:"icons/icon_tx_seminars_attendances.gif";s:4:"d892";s:37:"icons/icon_tx_seminars_checkboxes.gif";s:4:"f1f0";s:38:"icons/icon_tx_seminars_event_types.gif";s:4:"61a5";s:32:"icons/icon_tx_seminars_foods.gif";s:4:"1024";s:35:"icons/icon_tx_seminars_lodgings.gif";s:4:"5fdf";s:37:"icons/icon_tx_seminars_organizers.gif";s:4:"1e7e";s:42:"icons/icon_tx_seminars_payment_methods.gif";s:4:"44bd";s:44:"icons/icon_tx_seminars_seminars_complete.gif";s:4:"d4db";s:47:"icons/icon_tx_seminars_seminars_complete__h.gif";s:4:"ccf3";s:47:"icons/icon_tx_seminars_seminars_complete__t.gif";s:4:"a5cc";s:40:"icons/icon_tx_seminars_seminars_date.gif";s:4:"2cb2";s:43:"icons/icon_tx_seminars_seminars_date__h.gif";s:4:"f179";s:43:"icons/icon_tx_seminars_seminars_date__t.gif";s:4:"91be";s:41:"icons/icon_tx_seminars_seminars_topic.gif";s:4:"e4b1";s:44:"icons/icon_tx_seminars_seminars_topic__h.gif";s:4:"4689";s:44:"icons/icon_tx_seminars_seminars_topic__t.gif";s:4:"e220";s:32:"icons/icon_tx_seminars_sites.gif";s:4:"2694";s:35:"icons/icon_tx_seminars_speakers.gif";s:4:"ddc1";s:36:"icons/icon_tx_seminars_timeslots.gif";s:4:"bb73";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"eda4";s:17:"pi2/locallang.xml";s:4:"4ba6";s:20:"static/constants.txt";s:4:"f461";s:16:"static/setup.txt";s:4:"badc";s:40:"static/tx_srfeuserregister_pi1_tmpl.tmpl";s:4:"7ea9";s:14:"doc/manual.sxw";s:4:"ce86";s:38:"mod2/class.tx_seminars_backendlist.php";s:4:"5d85";s:30:"mod2/class.tx_seminars_csv.php";s:4:"ed81";s:37:"mod2/class.tx_seminars_eventslist.php";s:4:"dd46";s:41:"mod2/class.tx_seminars_organizerslist.php";s:4:"3995";s:44:"mod2/class.tx_seminars_registrationslist.php";s:4:"2775";s:39:"mod2/class.tx_seminars_speakerslist.php";s:4:"00d4";s:13:"mod2/conf.php";s:4:"32d2";s:14:"mod2/index.php";s:4:"33a9";s:18:"mod2/locallang.xml";s:4:"9165";s:22:"mod2/locallang_mod.xml";s:4:"4057";s:13:"mod2/mod2.css";s:4:"1fd1";s:19:"mod2/moduleicon.gif";s:4:"032e";s:38:"pi1/class.tx_seminars_event_editor.php";s:4:"fb6f";s:29:"pi1/class.tx_seminars_pi1.php";s:4:"0869";s:45:"pi1/class.tx_seminars_registration_editor.php";s:4:"09c0";s:20:"pi1/event_editor.xml";s:4:"e820";s:17:"pi1/locallang.xml";s:4:"b762";s:28:"pi1/registration_editor.html";s:4:"f1f6";s:27:"pi1/registration_editor.xml";s:4:"a27f";s:33:"pi1/registration_editor_step2.xml";s:4:"3ccf";s:20:"pi1/seminars_pi1.css";s:4:"7889";s:21:"pi1/seminars_pi1.tmpl";s:4:"bcc4";}',
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