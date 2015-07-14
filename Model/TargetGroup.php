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
 * This class represents a target group.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_TargetGroup extends tx_oelib_Model implements tx_seminars_Interface_Titled {
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
	 * @param string $title our title to set, must not be empty
	 *
	 * @return void
	 */
	public function setTitle($title) {
		if ($title == '') {
			throw new InvalidArgumentException('The parameter $title must not be empty.', 1333297060);
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
	 * @param tx_seminars_Model_FrontEndUser $frontEndUser the owner of this model to set
	 *
	 * @return void
	 */
	public function setOwner(tx_seminars_Model_FrontEndUser $frontEndUser) {
		$this->set('owner', $frontEndUser);
	}

	/**
	 * Returns this target group's minimum age.
	 *
	 * @return int this target group's minimum age, will be >= 0; will be 0
	 *                 if no minimum age has been set
	 */
	public function getMinimumAge() {
		return $this->getAsInteger('minimum_age');
	}

	/**
	 * Sets this target group's minimum age.
	 *
	 * @param int $minimumAge
	 *        this target group's minimum age, must be >= 0; set to 0 to unset the minimum age
	 *
	 * @return void
	 */
	public function setMinimumAge($minimumAge) {
		$this->setAsInteger('minimum_age', $minimumAge);
	}

	/**
	 * Returns this target group's maximum age.
	 *
	 * @return int this target group's maximum age, will be >= 0; will be 0
	 *                 if no maximum age has been set
	 */
	public function getMaximumAge() {
		return $this->getAsInteger('maximum_age');
	}

	/**
	 * Sets this target group's maximum age.
	 *
	 * @param int $maximumAge
	 *        this target group's maximum age, must be >= 0; set to 0 to unset the maximum age
	 *
	 * @return void
	 */
	public function setMaximumAge($maximumAge) {
		$this->setAsInteger('maximum_age', $maximumAge);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/TargetGroup.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/TargetGroup.php']);
}