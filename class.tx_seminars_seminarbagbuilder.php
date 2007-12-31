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
/**
 * Class 'tx_seminars_seminarbagbuilder' for the 'seminars' extension.
 *
 * This builder class creates customized seminarbag objects.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_bagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');

class tx_seminars_seminarbagbuilder extends tx_seminars_bagbuilder {
	/** class name of the bag class that will be built */
	var $bagClassName = 'tx_seminars_seminarbag';

	/**
	 * Configures the seminar bag to work like a BE list: It will use the
	 * default sorting in the BE, and hidden records will be shown.
	 *
	 * @access	public
	 */
	function setBackEndMode() {
		$this->useBackEndSorting();
		parent::setBackEndMode();
	}

	/**
	 * Sets the sorting to be the same as in the BE.
	 *
	 * @access	private
	 */
	function useBackEndSorting() {
		// unserializes the configuration array
		$globalConfiguration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']
		);
		$this->orderBy = ($globalConfiguration['useManualSorting'])
			? 'sorting' : 'begin_date';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']);
}

?>
