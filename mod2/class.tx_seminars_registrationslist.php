<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'registrations list' for the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once(t3lib_extMgm::extPath('seminars').'mod2/class.tx_seminars_backendlist.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');

class tx_seminars_registrationslist extends tx_seminars_backendlist {
	/** the registration which we want to list/show */
	var $registration;

	/**
	 * The constructor. Calls the constructor of the parent class and sets
	 * $this->tableName.
	 *
	 * @param	object		the current back-end page object
	 */
	function tx_seminars_registrationslist(&$page) {
		parent::tx_seminars_backendlist($page);
		$this->tableName = $this->tableAttendances;
	}

	/**
	 * Generates and prints out a registrations list.
	 *
	 * @return	string		the HTML source code to display
	 *
	 * @access	public
	 */
	function show() {
		global $LANG;

		// Initialize the variable for the HTML source code.
		$content = '';

		// Set the table layout of the event list.
		$tableLayout = array(
			'table' => array(
				TAB.TAB
					.'<table cellpadding="0" cellspacing="0" class="typo3-dblist">'.LF,
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
					.$LANG->getLL('registrationlist.feuser.name').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('registrationlist.seminar.title').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('registrationlist.seminar.date').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB.TAB
					.'&nbsp;'.LF
			)
		);

		// Initialize variables for the database query.
		$queryWhere = 'pid='.$this->page->pageInfo['uid'];
		$additionalTables = '';
		$orderBy = '';
		$limit = '';

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
					.$this->registration->getRecordIcon().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->registration->getUserName().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->registration->seminar->getTitle().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->registration->seminar->getDate().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->getEditIcon(
						$this->registration->getUid()
					)
					.$this->getDeleteIcon(
						$this->registration->getUid()
					).LF
			);
			$registrationBag->getNext();
		}

		$content .= $this->getNewIcon($this->page->pageInfo['uid']);

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->page->doc->table($table, $tableLayout);

		$content .= $registrationBag->checkConfiguration();

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_registrationslist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_registrationslist.php']);
}

?>
