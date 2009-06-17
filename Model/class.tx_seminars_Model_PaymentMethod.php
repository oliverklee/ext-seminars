<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_Model_PaymentMethod' for the 'seminars' extension.
 *
 * This class represents a payment method.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_PaymentMethod extends tx_oelib_Model {
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
	 * Returns our description.
	 *
	 * @return string our description, might be empty
	 */
	public function getDescription() {
		return $this->getAsString('description');
	}

	/**
	 * Sets our description.
	 *
	 * @param string our description to set, may be empty
	 */
	public function setDescription($description) {
		$this->setAsString('description', $description);
	}

	/**
	 * Returns whether this payment method has a description.
	 *
	 * @return boolean true if this payment method has a description, false
	 *                 otherwise
	 */
	public function hasDescription() {
		return $this->hasString('description');
	}

	/**
	 * Returns our owner.
	 *
	 * @return tx_seminars_Model_FrontEndUser the owner of this model, will be null
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_PaymentMethod.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_PaymentMethod.php']);
}
?>