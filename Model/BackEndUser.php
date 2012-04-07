<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Bernd Schönbach <bernd@oliverklee.de>
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
class tx_seminars_Model_BackEndUser extends tx_oelib_Model_BackEndUser implements tx_seminars_Interface_Titled {
	/**
	 * Returns the PID for newly created event records.
	 *
	 * This will be the first set PID found in the user's groups.
	 *
	 * @return integer the PID for newly created event records, will be 0 if no
	 *                 group has a PID set for new event records
	 */
	public function getEventFolderFromGroup() {
		return $this->getRecordFolderFromGroup('event');
	}

	/**
	 * Returns the PID for newly created registration records.
	 *
	 * This will be the first set PID found in the user's groups.
	 *
	 * @return integer the PID for newly created registration records, will be
	 *                 0 if no group has a PID set for new registration records
	 */
	public function getRegistrationFolderFromGroup() {
		return $this->getRecordFolderFromGroup('registration');
	}

	/**
	 * Returns the PID for newly created auxiliary records.
	 *
	 * This will be the first set PID found in the user's groups.
	 *
	 * @return integer the PID for newly created auxiliary records, will be
	 *                 0 if no group has a PID set for new auxiliary records
	 */
	public function getAuxiliaryRecordsFolder() {
		return $this->getRecordFolderFromGroup('auxiliary');
	}

	/**
	 * Returns the PID for newly created records of the given type.
	 *
	 * This will be the first set PID found in the user's groups.
	 *
	 * @param string $type
	 *        the type of the record, must be "event", "registration" or
	 *        "auxiliary"
	 *
	 * @return integer the PID for newly created records, will be 0 if no group
	 *                 has a PID set for new records of the given type
	 */
	private function getRecordFolderFromGroup($type) {
		$groups = $this->getAllGroups();
		if ($groups->isEmpty) {
			return 0;
		}

		$result = 0;

		foreach ($groups as $group) {
			switch ($type) {
				case 'event':
					$recordFolderPid = $group->getEventFolder();
					break;
				case 'registration':
					$recordFolderPid = $group->getRegistrationFolder();
					break;
				case 'auxiliary':
					$recordFolderPid = $group->getAuxiliaryRecordFolder();
					break;
				default:
					throw new InvalidArgumentException('The given record folder type "' . $type . '" was not valid.', 1333296088);
			}

			if ($recordFolderPid > 0) {
				$result = $recordFolderPid;
				break;
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/BackEndUser.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/BackEndUser.php']);
}
?>