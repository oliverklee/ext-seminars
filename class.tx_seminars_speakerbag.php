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
 * Class 'tx_seminars_speakerbag' for the 'seminars' extension.
 *
 * This aggregate class holds a bunch of speaker objects and allows
 * to iterate over them.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_bag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_speaker.php');

class tx_seminars_speakerbag extends tx_seminars_bag {
	/** Same as class name */
	var $prefixId = 'tx_seminars_speakerbag';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_speakerbag.php';

	/**
	 * The constructor. Creates a speaker bag that contains speaker
	 * records and allows to iterate over them.
	 *
	 * @param	string		string that will be prepended to the WHERE clause using AND, e.g. 'pid=42' (the AND and the enclosing spaces are not necessary for this parameter)
	 * @param	string		comma-separated names of additional DB tables used for JOINs, may be empty
	 * @param	string		GROUP BY clause (may be empty), must already by safeguarded against SQL injection
	 * @param	string		ORDER BY clause (may be empty), must already by safeguarded against SQL injection
	 * @param	string		LIMIT clause (may be empty), must already by safeguarded against SQL injection
	 *
	 * @access	public
	 */
	function tx_seminars_speakerbag($queryParameters = '1', $additionalTableNames = '', $groupBy = '', $orderBy = '', $limit = '') {
		// Although the parent class also calls init(), we need to call it
		// here already so that $this->tableSpeakers is provided.
		$this->init();
		parent::tx_seminars_bag($this->tableSpeakers, $queryParameters, $additionalTableNames, $groupBy, $orderBy, $limit);
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult as a source.
	 * If the current item cannot be created, $this->currentItem will be nulled out.
	 *
	 * $this->dbResult must be ensured to be non-null when this function is called.
	 *
	 * @access	protected
	 */
	function createItemFromDbResult() {
		$speakerClassname = t3lib_div::makeInstanceClassName('tx_seminars_speaker');
		$this->currentItem =& new $speakerClassname(0, $this->dbResult);
		$this->checkCurrentItem();

		return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_speakerbag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_speakerbag.php']);
}

?>
