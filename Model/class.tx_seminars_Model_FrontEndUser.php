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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_FrontEndUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_FrontEndUser.php']);
}
?>