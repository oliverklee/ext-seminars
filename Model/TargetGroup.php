<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_Model_TargetGroup' for the 'seminars' extension.
 *
 * This class represents a target group.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_TargetGroup extends tx_oelib_Model {
	/**
	 * Returns our title.
	 *
	 * @return string our title, will not be empty
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Sets our title.
	 *
	 * @param string our title to set, must not be empty
	 */
	public function setTitle($title) {
		if ($title == '') {
			throw new Exception('The parameter $title must not be empty.');
		}

		$this->setAsString('title', $title);
	}

	/**
	 * Returns our owner.
	 *
	 * @return tx_seminars_Model_FrontEndUser the owner of this model, will be NULL
	 *                                     if this model has no owner
	 */
	public function getOwner() {
		return $this->getAsModel('owner');
	}

	/**
	 * Sets our owner.
	 *
	 * @param tx_seminars_Model_FrontEndUser $frontEndUser the owner of this model
	 *                                                  to set
	 */
	public function setOwner(tx_seminars_Model_FrontEndUser $frontEndUser) {
		$this->set('owner', $frontEndUser);
	}

	/**
	 * Returns this target group's minimum age.
	 *
	 * @return integer this target group's minimum age, will be >= 0; will be 0
	 *                 if no minimum age has been set
	 */
	public function getMinimumAge() {
		return $this->getAsInteger('minimum_age');
	}

	/**
	 * Sets this target group's minimum age.
	 *
	 * @param integer $minimumAge
	 *        this target group's minimum age, must be >= 0; set to 0 to unset
	 *        the minimum age
	 */
	public function setMinimumAge($minimumAge) {
		$this->setAsInteger('minimum_age', $minimumAge);
	}

	/**
	 * Returns this target group's maximum age.
	 *
	 * @return integer this target group's maximum age, will be >= 0; will be 0
	 *                 if no maximum age has been set
	 */
	public function getMaximumAge() {
		return $this->getAsInteger('maximum_age');
	}

	/**
	 * Sets this target group's maximum age.
	 *
	 * @param integer $maximumAge
	 *        this target group's maximum age, must be >= 0; set to 0 to unset
	 *        the maximum age
	 */
	public function setMaximumAge($maximumAge) {
		$this->setAsInteger('maximum_age', $maximumAge);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/TargetGroup.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/TargetGroup.php']);
}
?>