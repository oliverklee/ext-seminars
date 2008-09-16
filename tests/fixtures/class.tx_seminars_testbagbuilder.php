<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_bagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_testbag.php');

/**
 * Class 'tx_seminars_testbagbuilder' for the 'seminars' extension.
 *
 * This builder class creates customized testbag objects.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_testbagbuilder extends tx_seminars_bagbuilder {
	/**
	 * @var	string		class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_testbag';

	/**
	 * Limits the bag to records with a particular title.
	 *
	 * @param	string		title which the bag elements must match, may be
	 * 						empty, must already be SQL-safe
	 */
	public function limitToTitle($title) {
		$this->whereClauseParts['title'] = 'title = "' . $title . '"';
	}

	/**
	 * Returns the additional table names.
	 *
	 * @return	array		the additional table names, may be empty
	 */
	public function getAdditionalTableNames() {
		return $this->additionalTableNames;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_testbagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_testbagbuilder.php']);
}
?>