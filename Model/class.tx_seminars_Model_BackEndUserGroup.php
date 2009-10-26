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
 * Class 'tx_seminars_Model_BackEndUserGroup' for the 'seminars' extension.
 *
 * This class represents a back-end usergroup.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Model_BackEndUserGroup extends tx_oelib_Model_BackEndUserGroup {
	/**
	 * Returns the PID for the storage of new events.
	 *
	 * @return integer the PID for the storage of new events, will be 0 if no
	 *                 PID has been set
	 */
	public function getEventFolder() {
		return $this->getAsInteger('tx_seminars_events_folder');
	}

	/**
	 * Returns the PID for the storage of new registrations.
	 *
	 * @return integer the PID for the storage of new registrations, will be 0
	 *                 if no PID has been set
	 */
	public function getRegistrationFolder() {
		return $this->getAsInteger('tx_seminars_registrations_folder');
	}

	/**
	 * Returns the PID for the storage of auxiliary records.
	 *
	 * Auxiliary records are all seminars record types with the exception of
	 * events and registrations.
	 *
	 * @return integer the PID for the storage of new auxiliary records, will
	 *                 be 0 if no PID has been set
	 */
	public function getAuxiliaryRecordFolder() {
		return $this->getAsInteger('tx_seminars_auxiliaries_folder');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_BackEndUserGroup.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_BackEndUserGroup.php']);
}
?>