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
 * This class represents a front-end user group.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_FrontEndUserGroup extends tx_oelib_Model_FrontEndUserGroup implements tx_seminars_Interface_Titled {
	/**
	 * @var int the publish setting to immediately publish all events edited
	 */
	const PUBLISH_IMMEDIATELY = 0;

	/**
	 * @var int the publish setting for hiding only new events created
	 */
	const PUBLISH_HIDE_NEW = 1;

	/**
	 * @var int the publish setting for hiding newly created and edited
	 *              events
	 */
	const PUBLISH_HIDE_EDITED = 2;

	/**
	 * Returns the setting for event publishing.
	 *
	 * If no publish settings have been set, PUBLISH_IMMEDIATELY is returned.
	 *
	 * @return int the class constants PUBLISH_IMMEDIATELY, PUBLISH_HIDE_NEW
	 *                 or PUBLISH_HIDE_EDITED
	 */
	public function getPublishSetting() {
		return $this->getAsInteger('tx_seminars_publish_events');
	}

	/**
	 * Returns the PID where to store the auxiliary records created by this
	 * front-end user group.
	 *
	 * @return int the PID where to store the auxiliary records created by
	 *                 this front-end user group, will be 0 if no PID is set
	 */
	public function getAuxiliaryRecordsPid() {
		return $this->getAsInteger('tx_seminars_auxiliary_records_pid');
	}

	/**
	 * Returns whether this user group has a PID for auxiliary records set.
	 *
	 * @return bool TRUE if this user group has PID for auxiliary records set,
	 *                 FALSE otherwise
	 */
	public function hasAuxiliaryRecordsPid() {
		return $this->hasInteger('tx_seminars_auxiliary_records_pid');
	}

	/**
	 * Checks whether this user group has a reviewer set.
	 *
	 * @return bool TRUE if a reviewer is set, FALSE otherwise
	 */
	public function hasReviewer() {
		return $this->getReviewer() !== NULL;
	}

	/**
	 * Returns the BE user which is stored as reviewer for this group.
	 *
	 * @return tx_oelib_Model_BackEndUser the reviewer for this group, will be
	 *                                    NULL if no reviewer has been set
	 */
	public function getReviewer() {
		return $this->getAsModel('tx_seminars_reviewer');
	}

	/**
	 * Checks whether this user group has a storage PID for event records set.
	 *
	 * @return bool TRUE if this user group has a event storage PID, FALSE
	 *                  otherwise
	 */
	public function hasEventRecordPid() {
		return $this->hasInteger('tx_seminars_events_pid');
	}

	/**
	 * Gets this user group's storage PID for event records.
	 *
	 * @return int the PID for the storage of event records, will be zero
	 *                 if no PID has been set
	 */
	public function getEventRecordPid() {
		return $this->getAsInteger('tx_seminars_events_pid');
	}

	/**
	 * Gets this user group's assigned default categories.
	 *
	 * @return tx_oelib_List the list of default categories assigned to this
	 *                       group, will be empty if no default categories are
	 *                       assigned to this group
	 */
	public function getDefaultCategories() {
		return $this->getAsList('tx_seminars_default_categories');
	}

	/**
	 * Checks whether this user group has default categories assigned.
	 *
	 * @return bool TRUE if this group has at least one default category,
	 *                 FALSE otherwise
	 */
	public function hasDefaultCategories() {
		return !$this->getDefaultCategories()->isEmpty();
	}

	/**
	 * Returns this user group's default organizer for the FE editor.
	 *
	 * @return tx_seminars_Model_Organizer this group's default organizer, will
	 *                                     be NULL if no organizer has been set
	 */
	public function getDefaultOrganizer() {
		return $this->getAsModel('tx_seminars_default_organizer');
	}

	/**
	 * Checks whether this user group has a default organizer set.
	 *
	 * @return bool TRUE if this group has a default organizer, FALSE
	 *                 otherwise
	 */
	public function hasDefaultOrganizer() {
		return ($this->getDefaultOrganizer()
			instanceof tx_seminars_Model_Organizer);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/FrontEndUserGroup.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/FrontEndUserGroup.php']);
}