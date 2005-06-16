<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module 'Seminars' for the 'seminars' extension.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:seminars/mod1/locallang.php');
#include ('locallang.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once (PATH_t3lib.'class.t3lib_page.php');
$BE_USER->modAccess($MCONF, 1);	// This checks permissions and exits if the users has no permission for entry.

class tx_seminars_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * @return	[type]		...
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	[type]		...
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'updateStats' => $LANG->getLL('menu_updateStats'),
// removed until the corresponding parts are functional
//				'listSpeakers' => $LANG->getLL('menu_listSpeakers'),
//				'listSites' => $LANG->getLL('menu_listSites'),
//				'listSeminars' => $LANG->getLL('menu_listSeminars'),
//				'seminarDetails' => $LANG->getLL('menu_seminarDetails'),
			)
		);
		parent::menuConfig();
	}

	// If you chose 'web' as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Writes the content to $this->content
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);

			// Render content:
			$this->moduleContent();

			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	[type]		...
	 */
	function printContent() {
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content.
	 *
	 * @return	[type]		...
	 */
	function moduleContent() {
		global $LANG;

		switch ((string)$this->MOD_SETTINGS['function']) {
			case 'updateStats':
			$content='<div><strong>Update stats</strong></div><BR>
					The <em>Kickstarter</em> has made this module automatically, it contains a default framework for a backend module but apart from it does nothing useful until you open the script '.substr(t3lib_extMgm::extPath('seminars'),strlen(PATH_site)).'mod1/index.php and edit it!
					<HR>
					<BR>This is the GET/POST vars sent to the script:<BR>'.
					'GET:'.t3lib_div::view_array($GLOBALS['HTTP_GET_VARS']).'<BR>'.
					'POST:'.t3lib_div::view_array($GLOBALS['HTTP_POST_VARS']).'<BR>'.
					'';
				$content = $this->updateStats();
				$this->content.=$this->doc->section($LANG->getLL('menu_updateStats'),$content,0,1);
			break;
			case 'listSpeakers':
				$content='<div align=center><strong>List Speakers</strong></div>';
				$this->content.=$this->doc->section('Message #2:',$content,0,1);
			break;
			case 'listSites':
				$content='<div align=center><strong>List Sites</strong></div>';
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
			break;
			case 'listSeminars':
				$content='<div align=center><strong>List Seminars</strong></div>';
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
			break;
		}
	}

	/**
	 * Updates the seminar statistics and displays success/failure.
	 *
	 * @return	[type]		...
	 */
	function updateStats() {
		$tableSeminars = 'tx_seminars_seminars';
		$tableAttendances = 'tx_seminars_attendances';
		$tableUsers = 'fe_users';
		$result = '';
		$alsoNoticeUnpaidRegistrations = true;

		$result .= '<h3>Anmeldezahlen werden aktualisiert</h3>';
		$seminars = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$tableSeminars,
			'1'.t3lib_pageSelect::enableFields($tableSeminars),
			'',
			'begin_date',
			'' );

		while ($currentSeminar = mysql_fetch_assoc($seminars)) {
			$result .= '<h4>'.htmlspecialchars($currentSeminar['title']).'</h4>';
			$numberOfAttendees = mysql_fetch_assoc($GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*) AS num',
				$tableAttendances,
				'seminar='.$GLOBALS['TYPO3_DB']->quoteStr($currentSeminar['uid'], $tableAttendances)
				.t3lib_pageSelect::enableFields($tableAttendances),
				'',
				'',
				'' ));
			$result .= '<p>Anzahl Teilnehmer: '.$numberOfAttendees[num].'</p>';

			$numberOfAttendeesPaid = mysql_fetch_assoc($GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS num',
			$tableAttendances,
			'seminar='.$GLOBALS['TYPO3_DB']->quoteStr($currentSeminar['uid'], $tableAttendances)
				.' AND paid=1'
				.t3lib_pageSelect::enableFields($tableAttendances),
			'',
			'',
			'' ));
			$result .= '<p>Anzahl Teilnehmer (bezahlt): '.$numberOfAttendeesPaid[num].'</p>';

			$numberOfSeenAttendees = $alsoNoticeUnpaidRegistrations ? $numberOfAttendees[num] : $numberOfAttendeesPaid[num];

			$hasEnoughAttendees = ($numberOfSeenAttendees >= $currentSeminar['attendees_min']) ? 1 : 0;
			$isFull = ($numberOfSeenAttendees >= $currentSeminar['attendees_max']) ? 1 : 0;

			$result .= '<p>Hat genug Teilis: '.$hasEnoughAttendees.'</p>';
			$result .= '<p>Ist voll: '.$isFull.'</p>';

			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$tableSeminars,
				'uid='.$GLOBALS['TYPO3_DB']->quoteStr($currentSeminar['uid'], $tableSeminars),
				array(
					'attendees' => $numberOfSeenAttendees,
					'enough_attendees' => $hasEnoughAttendees,
					'is_full' => $isFull,
				)
			);
		}

		$result .= '<h3>Titel der Anmeldungen werden aktualisiert</h3>';
		$attendances = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$tableAttendances,
			'1'.t3lib_pageSelect::enableFields($tableAttendances),
			'',
			'',
			'' );

		while ($currentAttendance = mysql_fetch_assoc($attendances)) {
			$attendeeName = mysql_fetch_assoc($GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'name',
				$tableUsers,
				'uid='.$GLOBALS['TYPO3_DB']->quoteStr($currentAttendance['user'], $tableUsers)
					.t3lib_pageSelect::enableFields($tableUsers),
				'',
				'',
				''));
			$seminarData = mysql_fetch_assoc($GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title, begin_date',
				$tableSeminars,
				'uid='.$GLOBALS['TYPO3_DB']->quoteStr($currentAttendance['seminar'], $tableSeminars)
					.t3lib_pageSelect::enableFields($tableSeminars),
				'',
				'',
				''));
			$newTitle = $attendeeName['name'].' / '.$seminarData['title'].' '.strftime('%d.%m.%Y', $seminarData['begin_date']);
			$result .= '<p>'.$newTitle.'</p>';
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$tableAttendances,
				'uid='.$GLOBALS['TYPO3_DB']->quoteStr($currentAttendance['uid'], $tableAttendances),
				array('title' => $GLOBALS['TYPO3_DB']->quoteStr($newTitle, $tableAttendances))
			);
		}
		return $result;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod1/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_seminars_module1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>