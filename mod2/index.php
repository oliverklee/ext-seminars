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
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speakerbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speaker.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_organizerbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_organizer.php');

$LANG->includeLLFile('EXT:lang/locallang_show_rechis.xml');
$LANG->includeLLFile('EXT:seminars/mod2/locallang.php');

// This checks permissions and exits if the users has no permission for entry.
$BE_USER->modAccess($MCONF, 1);

class tx_seminars_module2 extends t3lib_SCbase {
	/** the seminar which we want to list/show */
	var $seminar;

	/** the speaker which we want to list/show */
	var $speaker;

	/** the organizer which we want to list/show */
	var $organizer;

	/** the registration which we want to list/show */
	var $registration;

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
		global $LANG, $BACK_PATH;

		$this->content = '';

		// Read the selected sub module (from the tab menu) and make it available within this class.
		$this->subModule = t3lib_div::_GET('subModule');

		/**
		 * This variable will hold the information about the page. It will only be filled with values
		 * if the user has access to the page.
		 */
		$pageInfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		// Access check:
		// The page will show only if there is a valid page and if this page may
		// be viewed by the user.
		$hasAccess = is_array($pageInfo);

		if (($this->id && $hasAccess) || ($BE_USER->user['admin'])) {
			// start the document
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="POST">';
			$this->doc->docType = 'xhtml_strict';

			// draw the header
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);

			// define the sub modules that should be available in the tabmenu
			$this->availableSubModules = array();
			$this->availableSubModules[1] = $LANG->getLL('subModuleTitle_events');
			$this->availableSubModules[2] = $LANG->getLL('subModuleTitle_registrations');
			$this->availableSubModules[3] = $LANG->getLL('subModuleTitle_speakers');
			$this->availableSubModules[4] = $LANG->getLL('subModuleTitle_organizers');

			// generate the tab menu
			$this->content .= $this->doc->getTabMenu(array('id' => $this->id),
				'subModule',
				$this->subModule,
				$this->availableSubModules);
			$this->content .= $this->doc->spacer(5);

			// Select which sub module to display.
			// If no sub module is specified, a default page will be displayed.
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
				// The fallthrough is intentional.
				default:
					$this->content .= $this->showEventsList();
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
		global $LANG;

		// Initialize the variable for the HTML source code.
		$content = '';

		// Initialize variables for the database query.
		$queryWhere = 'pid='.$this->id;
		$additionalTables = '';
		$orderBy = 'sorting';
		$limit = '';

		// Set the table layout of the event list.
		$tableLayout = array(
			'table' => array(
				'<table cellpadding="0" cellspacing="0" width="100%" '
					.'class="typo3-dblist">',
				'</table>'
			),
			array(
				'tr' => array('<thead><tr>', '</tr></thead>'),
				'defCol' => array('<td class="c-headLineTable">', '</td>'),
			),
			'defRow' => array(
				'tr' => array('<tr>', '</tr>'),
				'defCol' => array('<td>', '</td>'),
			),
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.date').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.title').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.attendees').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.attendees_min').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.attendees_max').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.enough_attendees').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.is_full').'</span>',
			),
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
				$this->seminar->getDate(),
				t3lib_div::fixed_lgd_cs($this->seminar->getRealTitle(), 45),
				$this->seminar->getAttendances(),
				$this->seminar->getAttendancesMin(),
				$this->seminar->getAttendancesMax(),
				(!$this->seminar->hasEnoughAttendances()
					? $LANG->getLL('no') : $LANG->getLL('yes')),
				(!$this->seminar->isFull()
					? $LANG->getLL('no') : $LANG->getLL('yes')),
			);
			$seminarBag->getNext();
		}

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->doc->table($table, $tableLayout);

		$content .= $seminarBag->checkConfiguration();

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
				'<table cellpadding="0" cellspacing="0" width="100%" '
					.'class="typo3-dblist">',
				'</table>'
			),
			array(
				'tr' => array('<thead><tr>', '</tr></thead>'),
				'defCol' => array('<td class="c-headLineTable">', '</td>'),
			),
			'defRow' => array(
				'tr' => array('<tr>', '</tr>'),
				'defCol' => array('<td>', '</td>'),
			),
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('registrationlist.feuser.name').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('registrationlist.seminar.title').'</span>',
			),
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
				$this->registration->getUserName(),
				$this->registration->seminar->getRealTitle(),
			);
			$registrationBag->getNext();
		}

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
				'<table cellpadding="0" cellspacing="0" width="100%" '
					.'class="typo3-dblist">',
				'</table>'
			),
			array(
				'tr' => array('<thead><tr>', '</tr></thead>'),
				'defCol' => array('<td class="c-headLineTable">', '</td>'),
			),
			'defRow' => array(
				'tr' => array('<tr>', '</tr>'),
				'defCol' => array('<td>', '</td>'),
			),
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('speakerlist.title').'</span>',
			),
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
				$this->speaker->getTitle(),
			);
			$speakerBag->getNext();
		}

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
				'<table cellpadding="0" cellspacing="0" width="100%" '
					.'class="typo3-dblist">',
				'</table>'
			),
			array(
				'tr' => array('<thead><tr>', '</tr></thead>'),
				'defCol' => array('<td class="c-headLineTable">', '</td>'),
			),
			'defRow' => array(
				'tr' => array('<tr>', '</tr>'),
				'defCol' => array('<td>', '</td>'),
			),
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('organizerlist.title').'</span>',
			),
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
				$this->organizer->getTitle(),
			);
			$organizerBag->getNext();
		}

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->doc->table($table, $tableLayout);

		$content .= $organizerBag->checkConfiguration();

		return $content;
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
