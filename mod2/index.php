<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2007 Mario Rimann (typo3-coding@rimann.org)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module 'Events' for the 'seminars' extension.
 *
 * @author	Mario Rimann <typo3-coding@rimann.org>
 * @author	Niels Pardon <mail@niels-pardon.de>
 */

unset($MCONF);

require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_configgetter.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speakerbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speaker.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_organizerbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_organizer.php');
require_once(t3lib_extMgm::extPath('seminars').'pi2/class.tx_seminars_pi2.php');

$LANG->includeLLFile('EXT:lang/locallang_show_rechis.xml');
$LANG->includeLLFile('EXT:lang/locallang_mod_web_list.xml');
$LANG->includeLLFile('EXT:seminars/mod2/locallang.php');

// This checks permissions and exits if the users has no permission for entry.
$BE_USER->modAccess($MCONF, 1);

define('TAB', chr(9));

class tx_seminars_module2 extends t3lib_SCbase {
	/** the seminar which we want to list/show */
	var $seminar;

	/** the speaker which we want to list/show */
	var $speaker;

	/** the organizer which we want to list/show */
	var $organizer;

	/** the registration which we want to list/show */
	var $registration;

	/** Holds information about the current page. */
	var $pageInfo;

	/**
	 * Initializes some variables and also starts the initialization of the parent class.
	 *
	 * @access	public
	 */
	function init() {
		/*
		 * This is a workaround for the wrong generated links. The workaround is needed to
		 * get the right values from the GET Parameter. This workaround is from Elmar Hinz
		 * who also noted this in the bug tracker (http://bugs.typo3.org/view.php?id=2178).
		 */
		$matches = array();
		foreach ($GLOBALS['_GET'] as $key => $value) {
			if (preg_match('/amp;(.*)/', $key, $matches)) {
				$GLOBALS['_GET'][$matches[1]] = $value;
			}
		}
		/* --- END OF Workaround --- */

		parent::init();

		return;
	}

	/**
	 * Main function of the module. Writes the content to $this->content.
	 *
	 * No return value; output is directly written to the page.
	 *
	 * @access	public
	 */
	function main() {
		global $LANG, $BACK_PATH, $BE_USER;

		$this->content = '';

		/**
		 * This variable will hold the information about the page. It will only be filled with values
		 * if the user has access to the page.
		 */
		$this->pageInfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		// Access check:
		// The page will only be displayed if there is a valid page, if this
		// page may be viewed by the current BE user and if the static template
		// has been included or there actually are any records that will be
		// listed by this module on the current page.
		$hasAccess = is_array($this->pageInfo);

		if ((($this->id && $hasAccess) || ($BE_USER->user['admin']))
			&& $this->hasStaticTemplateOrRecords()) {
			// start the document
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="post">';
			$this->doc->docType = 'xhtml_strict';
			$this->doc->styleSheetFile2 = '../typo3conf/ext/seminars/mod2/mod2.css';

			// JavaScript function called within getDeleteIcon()
			$this->doc->JScode = '
				<script type="text/javascript">
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';

			// draw the header
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);

			$databasePlugin =& t3lib_div::makeInstance('tx_seminars_dbplugin');
			$databasePlugin->setTableNames();

			// define the sub modules that should be available in the tabmenu
			$this->availableSubModules = array();

			// only show the tabs if the back-end user has access to the corresponding tables
			if ($BE_USER->check('tables_select', $databasePlugin->tableSeminars)) {
				$this->availableSubModules[1] = $LANG->getLL('subModuleTitle_events');
			}

			if ($BE_USER->check('tables_select', $databasePlugin->tableAttendances)) {
				$this->availableSubModules[2] = $LANG->getLL('subModuleTitle_registrations');
			}

			if ($BE_USER->check('tables_select', $databasePlugin->tableSpeakers)) {
				$this->availableSubModules[3] = $LANG->getLL('subModuleTitle_speakers');
			}

			if ($BE_USER->check('tables_select', $databasePlugin->tableOrganizers)) {
				$this->availableSubModules[4] = $LANG->getLL('subModuleTitle_organizers');
			}

			// Read the selected sub module (from the tab menu) and make it available within this class.
			$this->subModule = intval(t3lib_div::_GET('subModule'));

			// If $this->subModule is not a key of $this->availableSubModules, 
			// set it to the key of the first element in $this->availableSubModules
			// so the first tab is activated.
			if (!array_key_exists($this->subModule, $this->availableSubModules)) {
				reset($this->availableSubModules);
				$this->subModule = key($this->availableSubModules);
			}

			// Only generate the tab menu if the current back-end user has the
			// rights to show any of the tabs.
			if ($this->subModule) {
				$this->content .= $this->doc->getTabMenu(array('id' => $this->id),
					'subModule',
					$this->subModule,
					$this->availableSubModules);
				$this->content .= $this->doc->spacer(5);
			}

			// Select which sub module to display.
			// If no sub module is specified, an empty page will be displayed.
			switch ($this->subModule) {
				case 2:
					$this->content .= $this->showRegistrationsList();
					break;
				case 3:
					$this->content .= $this->showSpeakersList();
					break;
				case 4:
					$this->content .= $this->showOrganizersList();
					break;
				case 1:
					$this->content .= $this->showEventsList();
				default:
					$this->content .= '';
					break;
			}

			// Finish the document (eg. add a closing html tag).
			$this->content .= $this->doc->endPage();
		} else {
			// The user doesn't have access.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);

			// end page
			$this->content .= $this->doc->endPage();
		}

		// Output the whole content.
		echo $this->content;
	}

	/**
	 * Generates and prints out an event list.
	 *
	 * @return	string		the HTML source code of the event list
	 *
	 * @access	public
	 */
	function showEventsList() {
		global $LANG, $BE_USER;

		// Initialize the variable for the HTML source code.
		$content = '';

		// unserialize the configuration array
		$globalConfiguration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']
		);

		// Initialize variables for the database query.
		$queryWhere = 'pid='.$this->id;
		$additionalTables = '';
		$orderBy = ($globalConfiguration['useManualSorting'])
			? 'sorting' : 'begin_date';
		$limit = '';

		// Set the table layout of the event list.
		$tableLayout = array(
			'table' => array(
				TAB.TAB
					.'<table cellpadding="0" cellspacing="0" class="typo3-dblist">'.chr(10),
				TAB.TAB
					.'</table>'.chr(10)
			),
			array(
				'tr' => array(
					TAB.TAB.TAB
						.'<thead>'.chr(10)
						.TAB.TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</tr>'.chr(10)
						.TAB.TAB.TAB
						.'</thead>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB.TAB
						.'<td class="c-headLineTable">'.chr(10),
					TAB.TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			),
			'defRow' => array(
				'tr' => array(
					TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB
						.'</tr>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="datecol">'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="attendees">'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="attendees_min">'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="attendees_max">'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="enough_attendees">'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="is_full">'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB
						.'<td>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			)
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'',
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.title').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.date').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'&nbsp;'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.attendees').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.attendees_min').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.attendees_max').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.enough_attendees').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.is_full').'</span>'.chr(10)
			)
		);

		$seminarBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminarbag');
		$seminarBag =& new $seminarBagClassname(
			$queryWhere,
			$additionalTables,
			'',
			$orderBy,
			$limit
		);

		while ($this->seminar =& $seminarBag->getCurrent()) {
			// Add the result row to the table array.
			$table[] = array(
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getRecordIcon().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.t3lib_div::fixed_lgd_cs(
						$this->seminar->getRealTitle(),
						$BE_USER->uc['titleLen']
					).chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getDate().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->getEditIcon(
						$this->seminar->tableName,
						$this->seminar->getUid()
					)
					.$this->getDeleteIcon(
						$this->seminar->tableName,
						$this->seminar->getUid()
					).chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->getRegistrationsCsvIcon()
					.$this->seminar->getAttendances().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getAttendancesMin().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getAttendancesMax().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.(!$this->seminar->hasEnoughAttendances()
					? $LANG->getLL('no') : $LANG->getLL('yes')).chr(10),
				TAB.TAB.TAB.TAB.TAB
					.(!$this->seminar->isFull()
					? $LANG->getLL('no') : $LANG->getLL('yes')).chr(10)
			);
			$seminarBag->getNext();
		}

		$content .= $this->getNewIcon($seminarBag->tableSeminars, $this->id);

		if ($seminarBag->objectCountWithoutLimit) {
			$content .= $this->getCsvIcon('events');
		}

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->doc->table($table, $tableLayout);

		// Check the BE configuration and the CSV export configuration.
		$content .= $seminarBag->checkConfiguration();
		$content .= $seminarBag->checkConfiguration(false, 'csv');

		return $content;
	}

	/**
	 * Generates and prints out a registrations list.
	 *
	 * @return	string		the HTML source code to display
	 *
	 * @access	public
	 */
	function showRegistrationsList() {
		global $LANG;

		// Initialize the variable for the HTML source code.
		$content = '';

		// Initialize variables for the database query.
		$queryWhere = 'pid='.$this->id;
		$additionalTables = '';
		$orderBy = '';
		$limit = '';

		// Set the table layout of the event list.
		$tableLayout = array(
			'table' => array(
				TAB.TAB
					.'<table cellpadding="0" cellspacing="0" class="typo3-dblist">'.chr(10),
				TAB.TAB
					.'</table>'.chr(10)
			),
			array(
				'tr' => array(
					TAB.TAB.TAB
						.'<thead>'.chr(10)
						.TAB.TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</tr>'.chr(10)
						.TAB.TAB.TAB
						.'</thead>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB.TAB
						.'<td class="c-headLineTable">'.chr(10),
					TAB.TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			),
			'defRow' => array(
				'tr' => array(
					TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB
						.'</tr>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB
						.'<td>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			)
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'',
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('registrationlist.feuser.name').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('registrationlist.seminar.title').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('registrationlist.seminar.date').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'&nbsp;'.chr(10)
			)
		);

		$registrationBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationbag');
		$registrationBag =& new $registrationBagClassname(
			$queryWhere,
			$additionalTables,
			'',
			$orderBy,
			$limit
		);

		while ($this->registration =& $registrationBag->getCurrent()) {
			$this->registration->getSeminar();
			// Add the result row to the table array.
			$table[] = array(
				TAB.TAB.TAB.TAB.TAB
					.$this->registration->getRecordIcon().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->registration->getUserName().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->registration->seminar->getRealTitle().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->registration->seminar->getDate().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->getEditIcon(
						$this->registration->tableName,
						$this->registration->getUid()
					)
					.$this->getDeleteIcon(
						$this->registration->tableName,
						$this->registration->getUid()
					).chr(10)
			);
			$registrationBag->getNext();
		}

		$content .= $this->getNewIcon($registrationBag->tableAttendances, $this->id);

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->doc->table($table, $tableLayout);

		$content .= $registrationBag->checkConfiguration();

		return $content;
	}

	/**
	 * Generates and prints out a speakers list.
	 *
	 * @return	string		the HTML source code to display
	 *
	 * @access	public
	 */
	function showSpeakersList() {
		global $LANG;

		// Initialize the variable for the HTML source code.
		$content = '';

		// Initialize variables for the database query.
		$queryWhere = 'pid='.$this->id;
		$additionalTables = '';
		$orderBy = '';
		$limit = '';

		// Set the table layout of the event list.
		$tableLayout = array(
			'table' => array(
				TAB.TAB
					.'<table cellpadding="0" cellspacing="0" class="typo3-dblist">'.chr(10),
				TAB.TAB
					.'</table>'.chr(10)
			),
			array(
				'tr' => array(
					TAB.TAB.TAB
						.'<thead>'.chr(10)
						.TAB.TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</tr>'.chr(10)
						.TAB.TAB.TAB
						.'</thead>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB.TAB
						.'<td class="c-headLineTable">'.chr(10),
					TAB.TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			),
			'defRow' => array(
				'tr' => array(
					TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB
						.'</tr>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB
						.'<td>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			)
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'',
				TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('speakerlist.title').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB
					.'&nbsp;'.chr(10)
			)
		);

		$speakerBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_speakerbag');
		$speakerBag =& new $speakerBagClassname(
			$queryWhere,
			$additionalTables,
			'',
			$orderBy,
			$limit
		);

		while ($this->speaker =& $speakerBag->getCurrent()) {
			// Add the result row to the table array.
			$table[] = array(
				TAB.TAB.TAB.TAB.TAB
					.$this->speaker->getRecordIcon().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->speaker->getTitle().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->getEditIcon(
						$this->speaker->tableName,
						$this->speaker->getUid()
					)
					.$this->getDeleteIcon(
						$this->speaker->tableName,
						$this->speaker->getUid()
					).chr(10)
			);
			$speakerBag->getNext();
		}

		$content .= $this->getNewIcon($speakerBag->tableSpeakers, $this->id);

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->doc->table($table, $tableLayout);

		$content .= $speakerBag->checkConfiguration();

		return $content;
	}

	/**
	 * Generates and prints out a organizers list.
	 *
	 * @return	string		the HTML source code to display
	 *
	 * @access	public
	 */
	function showOrganizersList() {
		global $LANG;

		// Initialize the variable for the HTML source code.
		$content = '';

		// Initialize variables for the database query.
		$queryWhere = 'pid='.$this->id;
		$additionalTables = '';
		$orderBy = '';
		$limit = '';

		// Set the table layout of the event list.
		$tableLayout = array(
			'table' => array(
				TAB.TAB
					.'<table cellpadding="0" cellspacing="0" class="typo3-dblist">'.chr(10),
				TAB.TAB
					.'</table>'.chr(10)
			),
			array(
				'tr' => array(
					TAB.TAB.TAB
						.'<thead>'.chr(10)
						.TAB.TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</tr>'.chr(10)
						.TAB.TAB.TAB
						.'</thead>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB.TAB
						.'<td class="c-headLineTable">'.chr(10),
					TAB.TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			),
			'defRow' => array(
				'tr' => array(
					TAB.TAB.TAB
						.'<tr>'.chr(10),
					TAB.TAB.TAB
						.'</tr>'.chr(10)
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB
						.'<td>'.chr(10),
					TAB.TAB.TAB.TAB
						.'</td>'.chr(10)
				)
			)
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'',
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('organizerlist.title').'</span>'.chr(10),
				TAB.TAB.TAB.TAB.TAB.TAB
					.'&nbsp;'.chr(10)
			)
		);

		$organizerBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_organizerbag');
		$organizerBag =& new $organizerBagClassname(
			$queryWhere,
			$additionalTables,
			'',
			$orderBy,
			$limit
		);

		while ($this->organizer =& $organizerBag->getCurrent()) {
			// Add the result row to the table array.
			$table[] = array(
				TAB.TAB.TAB.TAB.TAB
					.$this->organizer->getRecordIcon().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->organizer->getTitle().chr(10),
				TAB.TAB.TAB.TAB.TAB
					.$this->getEditIcon(
						$this->organizer->tableName,
						$this->organizer->getUid()
					)
					.$this->getDeleteIcon(
						$this->organizer->tableName,
						$this->organizer->getUid()
				).chr(10)
			);
			$organizerBag->getNext();
		}

		$content .= $this->getNewIcon($organizerBag->tableOrganizers, $this->id);

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->doc->table($table, $tableLayout);

		$content .= $organizerBag->checkConfiguration();

		return $content;
	}

	/**
	 * Generates an edit record icon which is linked to the edit view of
	 * a record.
	 *
	 * @param	string		the name of the table where the record is in
	 * @param	integer		the uid of the record
	 *
	 * @return	string		the HTML source code to return
	 *
	 * @access	public
	 */
	function getEditIcon($table, $uid) {
		global $BACK_PATH, $LANG, $BE_USER;

		$result = '';

		if ($BE_USER->check('tables_modify', $table)
			&& $BE_USER->doesUserHaveAccess(t3lib_BEfunc::getRecord('pages', $this->id), 16)) {
			$params = '&edit['.$table.']['.$uid.']=edit';
			$editOnClick = $this->editNewUrl($params, $BACK_PATH);
			$langEdit = $LANG->getLL('edit');
			$result = '<a href="'.htmlspecialchars($editOnClick).'">'
				.'<img '
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/edit2.gif',
					'width="11" height="12"')
				.' title="'.$langEdit.'" alt="'.$langEdit.'" class="icon" />'
				.'</a>';
		}

		return $result;
	}

	/**
	 * Generates a linked delete record icon whith a JavaScript confirmation
	 * window.
	 *
	 * @param	string		the name of the table where the record is in
	 * @param	integer		the uid of the record
	 *
	 * @return	string		the HTML source code to return
	 *
	 * @access	public
	 */
	function getDeleteIcon($table, $uid) {
		global $BACK_PATH, $LANG, $BE_USER;

		$result = '';

		if ($BE_USER->check('tables_modify', $table)
			&& $BE_USER->doesUserHaveAccess(t3lib_BEfunc::getRecord('pages', $this->id), 16)) {
			$params = '&cmd['.$table.']['.$uid.'][delete]=1';
			$confirmation = htmlspecialchars(
				'if (confirm('
				.$LANG->JScharCode(
					$LANG->getLL('deleteWarning')
					.t3lib_BEfunc::referenceCount(
						$table,
						$uid,
						' '.$LANG->getLL('referencesWarning')))
				.')) {return true;} else {return false;}');
			$langDelete = $LANG->getLL('delete', 1);
			$result = '<a href="'
				.htmlspecialchars($this->doc->issueCommand($params))
				.'" onclick="'.$confirmation.'">'
				.'<img'
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/garbage.gif',
					'width="11" height="12"'
				)
				.' title="'.$langDelete.'" alt="'.$langDelete.'" class="deleteicon" />'
				.'</a>';
		}

		return $result;
	}

	/**
	 * Returns a "create new record" image tag that is linked to the new record view.
	 *
	 * @param	string		the name of the table where the record should be saved to
	 * @param	integer		the page id where the record should be stored
	 *
	 * @return	string		the HTML source code to return
	 *
	 * @access	public
	 */
	function getNewIcon($table, $pid) {
		global $BACK_PATH, $LANG, $BE_USER;

		$result = '';

		if ($BE_USER->check('tables_modify', $table)
			&& $BE_USER->doesUserHaveAccess(t3lib_BEfunc::getRecord('pages', $this->id), 16)
			&& $this->pageInfo['doktype'] == 254) {
			$params = '&edit['.$table.']['.$pid.']=new';
			$editOnClick = $this->editNewUrl($params, $BACK_PATH);
			$langNew = $LANG->getLL('newRecordGeneral');
			$result = TAB.TAB
				.'<div id="typo3-newRecordLink">'.chr(10)
				.TAB.TAB.TAB
				.'<a href="'.htmlspecialchars($editOnClick).'">'.chr(10)
				.TAB.TAB.TAB.TAB
				.'<img'
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/new_record.gif',
					'width="7" height="4"')
				// We use an empty alt attribute as we already have a textual
				// representation directly next to the icon.
				.' title="'.$langNew.'" alt="" />'.chr(10)
				.TAB.TAB.TAB.TAB
				.$langNew.chr(10)
				.TAB.TAB.TAB
				.'</a>'.chr(10)
				.TAB.TAB
				.'</div>'.chr(10);
		}

		return $result;
	}

	/**
	 * Returns the url for the "create new record" link and the "edit record" link.
	 *
	 * @param	string		the parameters for tce
	 * @param	string		the back-path to the /typo3 directory
	 *
	 * @return	string		the url to return
	 *
	 * @access	protected
	 */
	function editNewUrl($params, $backPath = '') {
		$returnUrl = 'returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));

		return $backPath.'alt_doc.php?'.$returnUrl.$params;
	}

	/**
	 * Generates a linked CSV export icon for registrations from $this->seminar
	 * if that event has at least one registration and access to all involved
	 * registration records is granted.
	 *
	 * $this->seminar must be initialized when this function is called.
	 *
	 * @return	string		the HTML for the linked image (followed by a non-breaking space) or an empty string
	 *
	 * @access	public
	 */
	function getRegistrationsCsvIcon() {
		global $BACK_PATH, $LANG;

		static $accessChecker = null;
		if (!$accessChecker) {
			$accessChecker =& t3lib_div::makeInstance('tx_seminars_pi2');
			$accessChecker->init();
		}

		$result = '';

		$eventUid = $this->seminar->getUid();

		if ($this->seminar->hasAttendances()
			&& $accessChecker->canAccessListOfRegistrations($eventUid)) {
			$langCsv = $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.csv', 1);
			$result = '<a href="class.tx_seminars_csv.php?id='.$this->id
				.'&amp;tx_seminars_pi2[table]=registrations'
				.'&amp;tx_seminars_pi2[seminar]='.$eventUid.'">'
				.'<img'
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/csv.gif',
					'width="27" height="14"'
				)
				.' title="'.$langCsv.'" alt="'.$langCsv.'" class="icon" />'
				.'</a>&nbsp;';
		}

		return $result;
	}

	/**
	 * Checks whether this extension's static template is included on the
	 * current page or there is at least one event, attendance, organizer or
	 * speaker record (and be it even hidden or deleted) on the current page.
	 *
	 * @return	boolean		true if the static template has been included or there is at least one event, attendance, organizer or speaker record on the current page, false otherwise
	 *
	 * @access	protected
	 */
	function hasStaticTemplateOrRecords() {
		$configGetterClassname = t3lib_div::makeInstanceClassName('tx_seminars_configgetter');
		$configGetter =& new $configGetterClassname();
		$configGetter->init();

		$result = $configGetter->getConfValueBoolean('isStaticTemplateLoaded');

		// Only bother to check the existence of records on this page if there
		// is *no* static template.
		if (!$result) {
			$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
				'(SELECT COUNT(*) AS num FROM '.$configGetter->tableSeminars
					.' WHERE deleted=0 AND pid='.$this->id.') UNION '
					.'(SELECT COUNT(*) AS num FROM '.$configGetter->tableAttendances
					.' WHERE deleted=0 AND pid='.$this->id.') UNION '
					.'(SELECT COUNT(*) AS num FROM '.$configGetter->tableOrganizers
					.' WHERE deleted=0 AND pid='.$this->id.') UNION '
					.'(SELECT COUNT(*) AS num FROM '.$configGetter->tableSpeakers
					.' WHERE deleted=0 AND pid='.$this->id.')'
			);
			if ($dbResult) {
				$dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$result = ($dbResultRow['num'] > 0);
			}
		}

		return $result;
	}

	/**
	 * Returns a "CSV export" image tag that is linked to the CSV export,
	 * corresponding to the list that is visible in the BE.
	 *
	 * This icon is intended to be used next to the "create new record" icon.
	 *
	 * @param	string		the simplified name of the table from which the records should be exported, eg. "events"
	 *
	 * @return	string		the HTML source code of the linked CSV icon
	 *
	 * @access	protected
	 */
	function getCsvIcon($table) {
		global $BACK_PATH, $LANG;

		$langCsv = $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.csv', 1);
		$result = TAB.TAB
			.'<div id="typo3-csvLink">'.chr(10)
			.TAB.TAB.TAB
			.'<a href="class.tx_seminars_csv.php?id='.$this->id
			.'&amp;tx_seminars_pi2[table]='.$table
			.'&amp;tx_seminars_pi2[pid]='.$this->id.'">'.chr(10)
			.TAB.TAB.TAB.TAB
			.'<img'
			.t3lib_iconWorks::skinImg(
				$BACK_PATH,
				'gfx/csv.gif',
				'width="27" height="14"'
			)
			// We use an empty alt attribute as we already have a textual
			// representation directly next to the icon.
			.' title="'.$langCsv.'" alt="" />'.chr(10)
			.TAB.TAB.TAB.TAB
			.$langCsv.chr(10)
			.TAB.TAB.TAB
			.'</a>'.chr(10)
			.TAB.TAB
			.'</div>'.chr(10);

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_seminars_module2');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();

?>
