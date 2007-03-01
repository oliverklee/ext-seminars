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
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speakerbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speaker.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_organizerbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_organizer.php');

$LANG->includeLLFile('EXT:lang/locallang_show_rechis.xml');
$LANG->includeLLFile('EXT:lang/locallang_mod_web_list.xml');
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
		global $LANG, $BACK_PATH, $BE_USER;

		$this->content = '';

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

			// define the sub modules that should be available in the tabmenu
			$this->availableSubModules = array();

			// only show the tabs if the back-end user has access to the corresponding tables
			if ($BE_USER->check('tables_select', 'tx_seminars_seminars')) {
				$this->availableSubModules[1] = $LANG->getLL('subModuleTitle_events');
			}

			if ($BE_USER->check('tables_select', 'tx_seminars_attendances')) {
				$this->availableSubModules[2] = $LANG->getLL('subModuleTitle_registrations');
			}

			if ($BE_USER->check('tables_select', 'tx_seminars_speakers')) {
				$this->availableSubModules[3] = $LANG->getLL('subModuleTitle_speakers');
			}

			if ($BE_USER->check('tables_select', 'tx_seminars_organizers')) {
				$this->availableSubModules[4] = $LANG->getLL('subModuleTitle_organizers');
			}

			// Read the selected sub module (from the tab menu) and make it available within this class.
			$this->subModule = intval(t3lib_div::_GET('subModule'));

			// If $this->subModule is not a key of $this->availableSubModules, set it to 1 so the first tab is activated.
			if (!array_key_exists($this->subModule, $this->availableSubModules)) {
				$this->subModule = 1;
			}

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
				array('<td>', '</td>'),
				array('<td class="datecol">', '</td>'),
				'defCol' => array('<td>', '</td>'),
			),
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.title').'</span>',
				'<span style="color: #ffffff; font-weight: bold;">'.
					$LANG->getLL('eventlist.date').'</span>',
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
				'&nbsp;',
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
				t3lib_div::fixed_lgd_cs($this->seminar->getRealTitle(), 45),
				$this->seminar->getDate(),
				$this->seminar->getAttendances(),
				$this->seminar->getAttendancesMin(),
				$this->seminar->getAttendancesMax(),
				(!$this->seminar->hasEnoughAttendances()
					? $LANG->getLL('no') : $LANG->getLL('yes')),
				(!$this->seminar->isFull()
					? $LANG->getLL('no') : $LANG->getLL('yes')),
				$this->getEditIcon(
					$this->seminar->tableName,
					$this->seminar->getUid()
				)
					.$this->getDeleteIcon(
						$this->seminar->tableName,
						$this->seminar->getUid()
					),
			);
			$seminarBag->getNext();
		}

		$content .= $this->getNewIcon($seminarBag->tableSeminars, $this->id);

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
				'&nbsp;',
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
				$this->getEditIcon(
					$this->registration->tableName,
					$this->registration->getUid()
				)
					.$this->getDeleteIcon(
						$this->registration->tableName,
						$this->registration->getUid()
					),
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
				'&nbsp;',
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
				$this->getEditIcon(
					$this->speaker->tableName,
					$this->speaker->getUid()
				)
					.$this->getDeleteIcon(
						$this->speaker->tableName,
						$this->speaker->getUid()
					),
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
				'&nbsp;',
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
				$this->getEditIcon(
					$this->organizer->tableName,
					$this->organizer->getUid()
				)
					.$this->getDeleteIcon(
						$this->organizer->tableName,
						$this->organizer->getUid()
				),
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
			$result = '<a href="'.htmlspecialchars($editOnClick).'">'.
				'<img '
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/edit2.gif',
					'width="11" height="12"').
				' title="'.$langEdit.'" alt="'.$langEdit.'" class="icon" />'.
				'</a>';
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
				'if (confirm('.
				$LANG->JScharCode(
					$LANG->getLL('deleteWarning').
					t3lib_BEfunc::referenceCount(
						$table,
						$uid,
						' '.$LANG->getLL('referencesWarning'))).
				')) {return true;} else {return false;}');
			$langDelete = $LANG->getLL('delete', 1);
			$result = '<a href="'.$this->doc->issueCommand($params).'" onclick="'.$confirmation.'">'.
				'<img'.
				t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/garbage.gif',
					'width="11" height="12"').
				' title="'.$langDelete.'" alt="'.$langDelete.'" class="deleteicon" />'.
				'</a>';
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
			&& $BE_USER->doesUserHaveAccess(t3lib_BEfunc::getRecord('pages', $this->id), 16)) {
			$params = '&edit['.$table.']['.$pid.']=new';
			$editOnClick = $this->editNewUrl($params, $BACK_PATH);
			$langNew = $LANG->getLL('newRecordGeneral');
			$result = '<div id="typo3-newRecordLink">'.
				'<a href="'.htmlspecialchars($editOnClick).'">'.
				'<img'.
				t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/new_record.gif',
					'width="7" height="4"').
				' title="'.$langNew.'" alt="'.$langNew.'" />'.
				$langNew.
				'</a></div>';
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
