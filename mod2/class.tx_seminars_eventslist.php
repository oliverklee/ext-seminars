<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'events list' for the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'mod2/class.tx_seminars_backendlist.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars').'pi2/class.tx_seminars_pi2.php');

class tx_seminars_eventslist extends tx_seminars_backendlist {
	/** the table we're working on */
	protected $tableName = SEMINARS_TABLE_SEMINARS;

	/** the seminar which we want to list/show */
	private $seminar = null;

	/**
	 * Generates and prints out an event list.
	 *
	 * @return	string		the HTML source code of the event list
	 *
	 * @access	public
	 */
	function show() {
		global $LANG, $BE_USER;

		// Initialize the variable for the HTML source code.
		$content = '';

		// Set the table layout of the event list.
		$tableLayout = array(
			'table' => array(
				TAB.TAB
					.'<table cellpadding="0" cellspacing="0" class="typo3-dblist">'
					.LF,
				TAB.TAB
					.'</table>'.LF
			),
			array(
				'tr' => array(
					TAB.TAB.TAB
						.'<thead>'.LF
						.TAB.TAB.TAB.TAB
						.'<tr>'.LF,
					TAB.TAB.TAB.TAB
						.'</tr>'.LF
						.TAB.TAB.TAB
						.'</thead>'.LF
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB.TAB
						.'<td class="c-headLineTable">'.LF,
					TAB.TAB.TAB.TAB.TAB
						.'</td>'.LF
				)
			),
			'defRow' => array(
				'tr' => array(
					TAB.TAB.TAB
						.'<tr>'.LF,
					TAB.TAB.TAB
						.'</tr>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td>'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td>'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="datecol">'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td>'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="attendees">'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="attendees_min">'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="attendees_max">'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="enough_attendees">'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				array(
					TAB.TAB.TAB.TAB
						.'<td class="is_full">'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				),
				'defCol' => array(
					TAB.TAB.TAB.TAB
						.'<td>'.LF,
					TAB.TAB.TAB.TAB
						.'</td>'.LF
				)
			)
		);

		// Fill the first row of the table array with the header.
		$table = array(
			array(
				'',
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.title').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.date').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'&nbsp;'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.attendees').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.attendeesOnRegistrationQueue')
					.'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.attendees_min').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.attendees_max').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.enough_attendees').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('eventlist.is_full').'</span>'.LF
			)
		);

		$builder = t3lib_div::makeInstance('tx_seminars_seminarbagbuilder');
		$builder->setBackEndMode();
		$builder->setSourcePages($this->page->pageInfo['uid']);
		$seminarBag = $builder->build();

		$sortList = array();

		// unserialize the configuration array
		$globalConfiguration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']
		);

		$useManualSorting = $globalConfiguration['useManualSorting']
			&& $BE_USER->check('tables_modify', SEMINARS_TABLE_SEMINARS)
			&& $BE_USER->doesUserHaveAccess(
				t3lib_BEfunc::getRecord(
					'pages',
					$this->page->pageInfo['uid']
				),
				16
			);

		if ($useManualSorting) {
			// Initialize the array which holds the two previous records' UIDs.
			$previousUids = array(
				// will contain the UID of the predecessor of the current record
				0,
				// will contain the negative UID of the predecessor's predecessor
				// or the current PID
				0
			);

			while ($this->seminar =& $seminarBag->getCurrent()) {
				$uid = $this->seminar->getUid();

				// We can only set the "previous" and "next" elements in the
				// $sortList array if we already got the predecessor of the
				// current record in $previousUids[0]. This will be the case
				// after the first iteration.
				if ($previousUids[0]) {
					// Set the "previous" element of the current record to the
					// predecessor of the previous record.
					// This means when clicking on the "up" button the current
					// record will be moved after the predecessor of the previous
					// record.
					$sortList[$uid]['previous'] = $previousUids[1];

					// Set the "next" element of the previous record to the
					// negative UID of the current record.
					// This means when clicking on the "down" button the previous
					// record will be moved after the current record.
					$sortList[$previousUids[0]]['next'] = -$uid;
				}

				// Set the predecessor of the previous record to the negative
				// UID of the previous record if the previous record of the
				// current record is set already. Else set the predecessor of
				// the previous record to the PID.
				// That means if no predecessor of the previous record exists
				// than move the current record to top of the current page.
				$previousUids[1] = isset($sortList[$uid]['previous'])
					? -$previousUids[0] : $this->page->pageInfo['uid'];

				// Set previous record to the current record's UID.
				$previousUids[0] = $uid;

				// Get the next record and go to the start of the loop.
				$seminarBag->getNext();
			}
			$seminarBag->resetToFirst();
		}

		while ($this->seminar =& $seminarBag->getCurrent()) {
			// Add the result row to the table array.
			$table[] = array(
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getRecordIcon().LF,
				TAB.TAB.TAB.TAB.TAB
					.t3lib_div::fixed_lgd_cs(
						$this->seminar->getRealTitle(),
						$BE_USER->uc['titleLen']
					).LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getDate().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->getEditIcon(
						$this->seminar->getUid()
					).LF
					.TAB.TAB.TAB.TAB.TAB
					.$this->getDeleteIcon(
						$this->seminar->getUid()
					).LF
					.TAB.TAB.TAB.TAB.TAB
					.$this->getHideUnhideIcon(
						$this->seminar->getUid(),
						$this->seminar->isHidden()
					).LF
					.TAB.TAB.TAB.TAB.TAB
					.$this->getUpDownIcons(
						$useManualSorting,
						$sortList,
						$this->seminar->getUid()
					).LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->getRegistrationsCsvIcon()
					.$this->seminar->getAttendances().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getAttendancesOnRegistrationQueue().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getAttendancesMin().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->seminar->getAttendancesMax().LF,
				TAB.TAB.TAB.TAB.TAB
					.(!$this->seminar->hasEnoughAttendances()
					? $LANG->getLL('no') : $LANG->getLL('yes')).LF,
				TAB.TAB.TAB.TAB.TAB
					.(!$this->seminar->isFull()
					? $LANG->getLL('no') : $LANG->getLL('yes')).LF
			);
			$seminarBag->getNext();
		}

		$content .= $this->getNewIcon($this->page->pageInfo['uid']);

		if ($seminarBag->getObjectCountWithoutLimit()) {
			$content .= $this->getCsvIcon();
		}

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->page->doc->table($table, $tableLayout);

		// Check the BE configuration and the CSV export configuration.
		$content .= $seminarBag->checkConfiguration();
		$content .= $seminarBag->checkConfiguration(false, 'csv');

		return $content;
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
			$langCsv = $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.csv', 1);
			$result = '<a href="class.tx_seminars_csv.php?id='
				.$this->page->pageInfo['uid']
				.'&amp;tx_seminars_pi2[table]='.SEMINARS_TABLE_ATTENDANCES
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
	 * Generates a linked hide or unhide icon depending on the record's hidden
	 * status.
	 *
	 * @param	integer		the UID of the record, must be > 0
	 * @param	boolean		indicates if the record is hidden (true) or is
	 *						visible (false)
	 *
	 * @return	string		the HTML source code of the linked hide or unhide icon
	 *
	 * @access	protected
	 */
	function getHideUnhideIcon($uid, $hidden) {
		global $BACK_PATH, $LANG, $BE_USER;
		$result = '';

		if ($BE_USER->check('tables_modify', $this->tableName)
			&& $BE_USER->doesUserHaveAccess(
				t3lib_BEfunc::getRecord(
					'pages',
					$this->page->pageInfo['uid']
				),
				16
			)
		) {
			if ($hidden) {
				$params = '&data['.$this->tableName.']['.$uid.'][hidden]=0';
				$icon = 'gfx/button_unhide.gif';
				$langHide = $LANG->getLL('unHide');
			} else {
				$params = '&data['.$this->tableName.']['.$uid.'][hidden]=1';
				$icon = 'gfx/button_hide.gif';
				$langHide = $LANG->getLL('hide');
			}

			$result = '<a href="'
				.htmlspecialchars($this->page->doc->issueCommand($params)).'">'
				.'<img'
				.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					$icon,
					'width="11" height="12"'
				)
				.' title="'.$langHide.'" alt="'.$langHide.'" class="hideicon" />'
				.'</a>';
		}

		return $result;
	}

	/**
	 * Generates linked up and/or down icons depending on the manual sorting.
	 *
	 * @param	boolean		if true the linked up and/or down icons get generated
	 * 						else they won't get generated
	 * @param	array		An array which contains elements that have the record's
	 * 						UIDs as keys and an array with the two elements "previous"
	 * 						and "next" as values. The two elements' values are the
	 * 						negative UIDs of the records they should be moved after
	 * 						when the up (previous) or down (next) button is clicked.
	 * 						Except the second record's "previous" entry will be the
	 * 						PID of the current page so the record will be moved to
	 * 						the top of the current page when the up button is clicked.
	 * @param	integer		the UID of the current record, must be > 0
	 *
	 * @return	string		the HTML source code of the linked up and/or down
	 * 						icons (or an empty string if manual sorting is deactivated)
	 *
	 * @access	protected
	 */
	function getUpDownIcons($useManualSorting, array &$sortList, $uid) {
		$result = '';

		if ($useManualSorting) {
			$params = '&cmd['.$this->tableName.']['.$uid.'][move]=';

			$result = $this->getSingleUpOrDownIcon(
					'up',
					$params.$sortList[$uid]['previous'],
					$sortList[$uid]['previous']
				)
				.$this->getSingleUpOrDownIcon(
					'down',
					$params.$sortList[$uid]['next'],
					$sortList[$uid]['next']
				);
		}

		return $result;
	}

	/**
	 * Generates a single linked up or down icon depending on the type parameter.
	 *
	 * @param	string		the type of the icon ("up" or "down")
	 * @param	string		the command for TCEmain
	 * @param	integer		the negative UID of the record where the current record
	 * 						will be moved after if the button was clicked or the
	 * 						positive PID if the current icon is the second in the
	 * 						list and we should generate an up button
	 *
	 * @return	string		the HTML source code of a single linked up or down icon
	 *
	 * @access	protected
	 */
	function getSingleUpOrDownIcon($type, $params, $moveToUid) {
		global $LANG, $BACK_PATH;

		$result = '';

		if (isset($moveToUid)) {
			$result = '<a href="'.htmlspecialchars(
					$this->page->doc->issueCommand($params)
				).'">'
				.'<img'.t3lib_iconWorks::skinImg(
					$BACK_PATH,
					'gfx/button_'.$type.'.gif',
					'width="11" height="10"'
				).' title="'.$LANG->getLL('move'.ucfirst($type), 1).'"'
				.' alt="'.$LANG->getLL('move'.ucfirst($type), 1).'" />'
				.'</a>';
		} else {
			$result = '<span class="clearUpDownButton"></span>';
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_eventslist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_eventslist.php']);
}
?>
