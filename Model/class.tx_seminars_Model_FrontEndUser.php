<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Bernd Schönbach <bernd@oliverklee.de>
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
 * Class 'tx_seminars_Model_FrontEndUser' for the 'seminars' extension.
 *
 * This class represents a front-end user.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Model_FrontEndUser extends tx_oelib_Model_FrontEndUser {
	/**
	 * Returns the publish setting for the user groups the user is assigned to.
	 *
	 * The function will always return PUBLISH_IMMEDIATELY if the user has no
	 * groups.
	 *
	 * If the user has more than one group, the strictest setting of the groups
	 * will be returned.
	 *
	 * @return integer one of the class constants
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

		foreach ($userGroups as $userGroup) {
			$groupPermissions = $userGroup->getPublishSetting();

			$result = ($groupPermissions > $result)
				? $groupPermissions
				: $result;
		}

		return $result;
	}

	/**
	 * Returns the PID where to store the auxiliary records created by this
	 * front-end user.
	 *
	 * The PID is retrieved from the first user group which has a PID set.
	 *
	 * @return integer the PID where to store auxiliary records created by this
	 *                 front-end user, will be 0 if no PID is set
	 */
	public function getAuxiliaryRecordsPid() {
		if ($this->getUserGroups()->isEmpty()) {
			return 0;
		}

		$auxiliaryRecordsPid = 0;

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
	 * @return tx_oelib_Model_BackEndUser the reviewer set in the user's group,
	 *                                    will be null if no reviewer has been
	 *                                    set or the user has no groups
	 */
	public function getReviewerFromGroup() {
		$result = null;

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
	 * @return integer the PID for the event records to store, will be 0 if no
	 *                 event record PID has been set in any of this user's
	 *                 groups
	 */
	public function getEventRecordsPid() {
		if ($this->getUserGroups()->isEmpty()) {
			return 0;
		}

		$eventRecordPid = 0;

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
	 * @return tx_oelib_List the categories assigned to this user's groups, will
	 *                       be empty if no default categories have been assigned
	 *                       to any of the user's groups
	 */
	public function getDefaultCategoriesFromGroup() {
		$categories = tx_oelib_ObjectFactory::make('tx_oelib_List');

		foreach ($this->getUserGroups() as $group) {
			if ($group->hasDefaultCategories()) {
				$categories->appendUnique($group->getDefaultCategories());
			}
		}

		return $categories;
	}

	/**
	 * Checks whether this user's groups have any default categories.
	 *
	 * @return boolean TRUE if at least one of the user's groups has a default
	 *                 category, FALSE otherwise
	 */
	public function hasDefaultCategories() {
		return !$this->getDefaultCategoriesFromGroup()->isEmpty();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_FrontEndUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_FrontEndUser.php']);
}
?>