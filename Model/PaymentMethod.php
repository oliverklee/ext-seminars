<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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
 * This class represents a payment method.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_PaymentMethod extends tx_oelib_Model implements tx_seminars_Interface_Titled {
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
			throw new InvalidArgumentException('The parameter $title must not be empty.', 1333296882);
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
	 * @param string $description our description to set, may be empty
	 *
	 * @return void
	 */
	public function setDescription($description) {
		$this->setAsString('description', $description);
	}

	/**
	 * Returns whether this payment method has a description.
	 *
	 * @return boolean TRUE if this payment method has a description, FALSE
	 *                 otherwise
	 */
	public function hasDescription() {
		return $this->hasString('description');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/PaymentMethod.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/PaymentMethod.php']);
}
?>