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
 * Class 'tx_seminars_Model_Place' for the 'seminars' extension.
 *
 * This class represents a place.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_Place extends tx_oelib_Model {
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
	 * Returns our address.
	 *
	 * @return string our address, might be empty
	 */
	public function getAddress() {
		return $this->getAsString('address');
	}

	/**
	 * Sets our address.
	 *
	 * @param string our address to set, may be empty
	 */
	public function setAddress($address) {
		$this->setAsString('address', $address);
	}

	/**
	 * Returns whether this place has an address.
	 *
	 * @return boolean true if this address has an address, false otherwise
	 */
	public function hasAddress() {
		return $this->hasString('address');
	}

	/**
	 * Returns our city name.
	 *
	 * @return string the city name, will not be empty
	 */
	public function getCity() {
		return $this->getAsString('city');
	}

	/**
	 * Sets our city name.
	 *
	 * @param string our city name, must not be empty
	 */
	public function setCity($city) {
		if ($city == '') {
			throw new Exception('The parameter $city must not be empty.');
		}

		$this->setAsString('city', $city);
	}

	/**
	 * Returns the country of this place as tx_oelib_Model_Country.
	 *
	 * @return tx_oelib_Model_Country the country of this place
	 */
	public function getCountry() {
		$countryCode = $this->getAsString('country');
		if ($countryCode == '') {
			return null;
		}

		return tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->findByIsoAlpha2Code($countryCode);
	}

	/**
	 * Sets the country of this place.
	 *
	 * @param tx_oelib_Model_Country the country to set for this place
	 */
	public function setCountry(tx_oelib_Model_Country $country) {
		$this->set('country', $country->getIsoAlpha2Code());
	}

	/**
	 * Returns whether this place has a country.
	 *
	 * @return boolean true if this place has a country, false otherwise
	 */
	public function hasCountry() {
		return ($this->getCountry() instanceof tx_oelib_Model_Country);
	}

	/**
	 * Returns our homepage.
	 *
	 * @return string our homepage, may be empty
	 */
	public function getHomepage() {
		return $this->getAsString('homepage');
	}

	/**
	 * Sets our homepage.
	 *
	 * @param string our homepage, may be empty
	 */
	public function setHomepage($homepage) {
		$this->setAsString('homepage', $homepage);
	}

	/**
	 * Returns whether this organizer has a homepage.
	 *
	 * @return boolean true if this organizer has a homepage, false otherwise
	 */
	public function hasHomepage() {
		return $this->hasString('homepage');
	}

	/**
	 * Returns our directions.
	 *
	 * @return string our directions, might be empty
	 */
	public function getDirections() {
		return $this->getAsString('directions');
	}

	/**
	 * Sets our directions.
	 *
	 * @param string our directions to set, may be empty
	 */
	public function setDirections($directions) {
		$this->setAsString('directions', $directions);
	}

	/**
	 * Returns whether this place has directions.
	 *
	 * @return boolean true if this place has directions, false otherwise
	 */
	public function hasDirections() {
		return $this->hasString('directions');
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

	/**
	 * Returns our notes.
	 *
	 * @return string our notes, may be empty
	 */
	public function getNotes() {
		return $this->getAsString('notes');
	}

	/**
	 * Sets our notes.
	 *
	 * @param string $notes our notes to set, might be empty
	 */
	public function setNotes($notes) {
		$this->setAsString('notes', $notes);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_Place.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_Place.php']);
}
?>