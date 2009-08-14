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
 * Class 'tx_seminars_Model_FrontEndUserGroup' for the 'seminars' extension.
 *
 * This class represents a front-end usergroup.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_FrontEndUserGroup extends tx_oelib_Model_FrontEndUserGroup {
	/**
	 * @var integer the publish setting to immediately publish all events edited
	 */
	const PUBLISH_IMMEDIATELY = 0;

	/**
	 * @var integer the publish setting for hiding only new events created
	 */
	const PUBLISH_HIDE_NEW = 1;

	/**
	 * @var integer the publish setting for hiding newly created and edited
	 *              events
	 */
	const PUBLISH_HIDE_EDITED = 2;

	/**
	 * Returns the setting for event publishing.
	 *
	 * If no publish settings have been set, PUBLISH_IMMEDIATELY is returned.
	 *
	 * @return integer the class constants PUBLISH_IMMEDIATELY, PUBLISH_HIDE_NEW
	 *                 or PUBLISH_HIDE_EDITED
	 */
	public function getPublishSetting() {
		return $this->getAsInteger('tx_seminars_publish_events');
	}

	/**
	 * Returns the PID where to store the auxiliary records created by this
	 * front-end user group.
	 *
	 * @return integer the PID where to store the auxiliary records created by
	 *                 this front-end user group, will be 0 if no PID is set
	 */
	public function getAuxiliaryRecordsPid() {
		return $this->getAsInteger('tx_seminars_auxiliary_records_pid');
	}

	/**
	 * Returns whether this user group has a PID for auxiliary records set.
	 *
	 * @return boolean true if this user group has PID for auxiliary records set,
	 *                 false otherwise
	 */
	public function hasAuxiliaryRecordsPid() {
		return $this->hasInteger('tx_seminars_auxiliary_records_pid');
	}

	/**
	 * Checks whether this user group has a reviewer set.
	 *
	 * @return boolean true if a reviewer is set, false otherwise
	 */
	public function hasReviewer() {
		return $this->getReviewer() !== null;
	}

	/**
	 * Returns the BE user which is stored as reviewer for this group.
	 *
	 * @return tx_oelib_Model_BackEndUser the reviewer for this group, will be
	 *                                    null if no reviewer has been set
	 */
	public function getReviewer() {
		return $this->getAsModel('tx_seminars_reviewer');
	}

	/**
	 * Checks whether this user group has a storage PID for event records set.
	 *
	 * @return boolean true if this user group has a event storage PID, false
	 *                  otherwise
	 */
	public function hasEventRecordPid() {
		return $this->hasInteger('tx_seminars_events_pid');
	}

	/**
	 * Gets this user group's storage PID for event records.
	 *
	 * @return integer the PID for the storage of event records, will be zero
	 *                 if no PID has been set
	 */
	public function getEventRecordPid() {
		return $this->getAsInteger('tx_seminars_events_pid');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_FrontEndUserGroup.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_FrontEndUserGroup.php']);
}
?>