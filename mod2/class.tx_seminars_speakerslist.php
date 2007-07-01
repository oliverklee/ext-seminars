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
 * Class 'speakers list' for the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once(t3lib_extMgm::extPath('seminars').'mod2/class.tx_seminars_backendlist.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speakerbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speaker.php');

class tx_seminars_speakerslist extends tx_seminars_backendlist {
	/** the speaker which we want to list/show */
	var $speaker;

	/**
	 * The constructor. Calls the constructor of the parent class and sets
	 * $this->tableName.
	 * 
	 * @param	object		the current back-end page object
	 */
	function tx_seminars_speakerslist(&$page) {
		parent::tx_seminars_backendlist($page);
		$this->tableName = $this->tableSpeakers;
	}

	/**
	 * Generates and prints out a speakers list.
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

		// Initialize variables for the database query.
		$queryWhere = 'pid='.$this->page->pageInfo['uid'];
		$additionalTables = '';
		$orderBy = '';
		$limit = '';

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
						$this->speaker->getUid()
					)
					.$this->getDeleteIcon(
						$this->speaker->getUid()
					).chr(10)
			);
			$speakerBag->getNext();
		}

		$content .= $this->getNewIcon($this->page->pageInfo['uid']);

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->page->doc->table($table, $tableLayout);

		$content .= $speakerBag->checkConfiguration();

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_speakerslist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_speakerslist.php']);
}

?>
