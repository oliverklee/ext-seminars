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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'mod2/class.tx_seminars_backendlist.php');

/**
 * Class 'speakers list' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_mod2_speakerslist extends tx_seminars_backendlist {
	/** the table we're working on */
	protected $tableName = SEMINARS_TABLE_SPEAKERS;

	/** the speaker which we want to list/show */
	private $speaker = null;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->speaker) {
			$this->speaker->__destruct();
			unset($this->speaker);
		}

		parent::__destruct();
	}

	/**
	 * Generates and prints out a speakers list.
	 *
	 * @return string the HTML source code to display
	 */
	public function show() {
		global $LANG;

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
				TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('speakerlist.title').'</span>'.LF,
				TAB.TAB.TAB.TAB.TAB
					.'&nbsp;'.LF,
				TAB.TAB.TAB.TAB.TAB
					.'<span style="color: #ffffff; font-weight: bold;">'
					.$LANG->getLL('speakerlist.skills').'</span>'.LF
			)
		);

		// Initialize variables for the database query.
		$pageData = $this->page->getPageData();
		$queryWhere = 'pid=' . $pageData['uid'];
		$additionalTables = '';
		$orderBy = '';
		$limit = '';

		$speakerBagClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_speakerbag'
		);
		$speakerBag = new $speakerBagClassname(
			$queryWhere,
			$additionalTables,
			'',
			$orderBy,
			$limit
		);

		foreach ($speakerBag as $this->speaker) {
			// Add the result row to the table array.
			$table[] = array(
				TAB.TAB.TAB.TAB.TAB
					.$this->speaker->getRecordIcon().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->speaker->getTitle().LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->getEditIcon(
						$this->speaker->getUid()
					)
					.$this->getDeleteIcon(
						$this->speaker->getUid()
					).LF,
				TAB.TAB.TAB.TAB.TAB
					.$this->speaker->getSkillsShort().LF
			);
		}

		$content .= $this->getNewIcon($pageData['uid']);

		// Output the table array using the tableLayout array with the template
		// class.
		$content .= $this->page->doc->table($table, $tableLayout);

		$content .= $speakerBag->checkConfiguration();

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_speakerslist.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_speakerslist.php']);
}
?>