<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This class represents a back-end user group.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Model_BackEndUserGroup extends tx_oelib_Model_BackEndUserGroup implements tx_seminars_Interface_Titled {
	/**
	 * Returns the PID for the storage of new events.
	 *
	 * @return int the PID for the storage of new events, will be 0 if no
	 *                 PID has been set
	 */
	public function getEventFolder() {
		return $this->getAsInteger('tx_seminars_events_folder');
	}

	/**
	 * Returns the PID for the storage of new registrations.
	 *
	 * @return int the PID for the storage of new registrations, will be 0
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
	 * @return int the PID for the storage of new auxiliary records, will
	 *                 be 0 if no PID has been set
	 */
	public function getAuxiliaryRecordFolder() {
		return $this->getAsInteger('tx_seminars_auxiliaries_folder');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/BackEndUserGroup.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/BackEndUserGroup.php']);
}