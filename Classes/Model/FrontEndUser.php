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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a front-end user.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_FrontEndUser extends Tx_Oelib_Model_FrontEndUser {
	/**
	 * Returns the publish setting for the user groups the user is assigned to.
	 *
	 * The function will always return PUBLISH_IMMEDIATELY if the user has no
	 * groups.
	 *
	 * If the user has more than one group, the strictest setting of the groups
	 * will be returned.
	 *
	 * @return int one of the class constants
	 *                 tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
	 *                 tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW or
	 *                 tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
	 */
	public function getPublishSetting() {
		$userGroups = $this->getUserGroups();
		if ($userGroups->isEmpty()) {
			return tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY;
		}

		$result = tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY;

		/** @var tx_seminars_Model_FrontEndUserGroup $userGroup */
		foreach ($userGroups as $userGroup) {
			$groupPermissions = $userGroup->getPublishSetting();

			$result = ($groupPermissions > $result) ? $groupPermissions : $result;
		}

		return $result;
	}

	/**
	 * Returns the PID where to store the auxiliary records created by this
	 * front-end user.
	 *
	 * The PID is retrieved from the first user group which has a PID set.
	 *
	 * @return int the PID where to store auxiliary records created by this
	 *                 front-end user, will be 0 if no PID is set
	 */
	public function getAuxiliaryRecordsPid() {
		if ($this->getUserGroups()->isEmpty()) {
			return 0;
		}

		$auxiliaryRecordsPid = 0;

		/** @var tx_seminars_Model_FrontEndUserGroup $userGroup */
		foreach ($this->getUserGroups() as $userGroup) {
			if ($userGroup->hasAuxiliaryRecordsPid()) {
				$auxiliaryRecordsPid = $userGroup->getAuxiliaryRecordsPid();
				break;
			}
		}

		return $auxiliaryRecordsPid;
	}

	/**
	 * Returns the reviewer set in the groups of this user.
	 *
	 * Will return the first reviewer found.
	 *
	 * @return tx_seminars_Model_BackEndUser the reviewer set in the user's group,
	 *                                    will be NULL if no reviewer has been
	 *                                    set or the user has no groups
	 */
	public function getReviewerFromGroup() {
		$result = NULL;

		/** @var tx_seminars_Model_FrontEndUserGroup $userGroup */
		foreach ($this->getUserGroups() as $userGroup) {
			if ($userGroup->hasReviewer()) {
				$result = $userGroup->getReviewer();
				break;
			}
		}

		return $result;
	}

	/**
	 * Returns the PID where to store the event records for this user.
	 *
	 * This will return the first PID found for events in this user's groups.
	 *
	 * @return int the PID for the event records to store, will be 0 if no
	 *                 event record PID has been set in any of this user's
	 *                 groups
	 */
	public function getEventRecordsPid() {
		if ($this->getUserGroups()->isEmpty()) {
			return 0;
		}

		$eventRecordPid = 0;

		/** @var tx_seminars_Model_FrontEndUserGroup $userGroup */
		foreach ($this->getUserGroups() as $userGroup) {
			if ($userGroup->hasEventRecordPid()) {
				$eventRecordPid = $userGroup->getEventRecordPid();
				break;
			}
		}

		return $eventRecordPid;
	}

	/**
	 * Returns all default categories assigned to this user's groups.
	 *
	 * @return Tx_Oelib_List the categories assigned to this user's groups, will
	 *                       be empty if no default categories have been assigned
	 *                       to any of the user's groups
	 */
	public function getDefaultCategoriesFromGroup() {
		/** @var Tx_Oelib_List $categories */
		$categories = GeneralUtility::makeInstance('Tx_Oelib_List');

		/** @var tx_seminars_Model_FrontEndUserGroup $group */
		foreach ($this->getUserGroups() as $group) {
			if ($group->hasDefaultCategories()) {
				$categories->append($group->getDefaultCategories());
			}
		}

		return $categories;
	}

	/**
	 * Checks whether this user's groups have any default categories.
	 *
	 * @return bool TRUE if at least one of the user's groups has a default
	 *                 category, FALSE otherwise
	 */
	public function hasDefaultCategories() {
		return !$this->getDefaultCategoriesFromGroup()->isEmpty();
	}

	/**
	 * Returns all default organizers assigned to this user's groups.
	 *
	 * @return tx_oelib_List the organizers assigned to this user's groups, will
	 *                       be empty if no default organizers have been assigned
	 *                       to any of the user's groups
	 */
	public function getDefaultOrganizers() {
		/** @var Tx_Oelib_List $organizers */
		$organizers = GeneralUtility::makeInstance('Tx_Oelib_List');

		/** @var tx_seminars_Model_FrontEndUserGroup $group */
		foreach ($this->getUserGroups() as $group) {
			if ($group->hasDefaultOrganizer()) {
				$organizers->add($group->getDefaultOrganizer());
			}
		}

		return $organizers;
	}

	/**
	 * Checks whether this user's groups have any default organizers.
	 *
	 * @return bool TRUE if at least one of the user's groups has a default
	 *                 organizer, FALSE otherwise
	 */
	public function hasDefaultOrganizers() {
		return !$this->getDefaultOrganizers()->isEmpty();
	}

	/**
	 * Gets the registration record for which this user is related to as
	 * "additional registered person".
	 *
	 * @return tx_seminars_Model_Registration the associated registration,
	 *                                        might be NULL
	 */
	public function getRegistration() {
		return $this->getAsModel('tx_seminars_registration');
	}

	/**
	 * sets the registration record for which this user is related to as
	 * "additional registered person".
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the associated registration, may be NULL
	 *
	 * @return void
	 */
	public function setRegistration(tx_seminars_Model_Registration $registration = NULL) {
		$this->set('tx_seminars_registration', $registration);
	}
}