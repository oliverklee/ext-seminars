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
 * Class 'tx_seminars_categorybagbuilder' for the 'seminars' extension.
 *
 * This builder class creates customized categorybag objects.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_bagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_categorybag.php');

class tx_seminars_categorybagbuilder extends tx_seminars_bagbuilder {
	/** class name of the bag class that will be built */
	var $bagClassName = 'tx_seminars_categorybag';

	/** the sorting field */
	var $orderBy = 'title';

	/**
	 * Limits the bag to the categories of the event provided by the parameter
	 * $eventUid.
	 *
	 * Example: The event with the provided UID references categories 9 and 12.
	 * So the bag will be limited to categories 9 and 12 (plus any additional
	 * limits).
	 *
	 * @param	integer		UID of the event to which the category selection
	 * 						should be limited, must be > 0
	 *
	 * @access	public
	 */
	function limitToEvent($eventUid) {
		if ($eventUid <= 0) {
			return;
		}

		$this->whereClauseParts['event'] = 'EXISTS ('
			.'SELECT * FROM '.SEMINARS_TABLE_CATEGORIES_MM
			.' WHERE '.SEMINARS_TABLE_CATEGORIES_MM.'.uid_local='.$eventUid
			.' AND '.SEMINARS_TABLE_CATEGORIES_MM.'.uid_foreign='
			.SEMINARS_TABLE_CATEGORIES.'.uid'
			.')';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_categorybagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_categorybagbuilder.php']);
}

?>
