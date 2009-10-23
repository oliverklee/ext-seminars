<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
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
 * Class 'tx_seminars_Model_BackEndUser' for the 'seminars' extension.
 *
 * This class represents a back-end user.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Model_BackEndUser extends tx_oelib_Model_BackEndUser {
	/**
	 * Returns the PID for newly created event records.
	 *
	 * Returns the first PID found in the user's groups greater than zero.
	 *
	 * @return integer the PID for newly created event records, will be 0 if no
	 *                 group has a PID set for new event records
	 */
	public function getEventFolderFromGroup() {
		$groups = $this->getAllGroups();
		if ($groups->isEmpty) {
			return 0;
		}

		$result = 0;

		foreach ($groups as $group) {
			$eventFolderPid = $group->getEventFolder();
			if ($eventFolderPid > 0) {
				$result = $eventFolderPid;
				break;
			}
		}

		return $result;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_BackEndUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_BackEndUser.php']);
}
?>