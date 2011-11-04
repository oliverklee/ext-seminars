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
 * Class 'tx_seminars_Model_Speaker' for the 'seminars' extension.
 *
 * This class represents an speaker.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_Speaker extends tx_oelib_Model implements tx_oelib_Interface_MailRole {
	/**
	 * @var integer the gender type for speakers without gender
	 */
	const GENDER_UNKNOWN = 0;

	/**
	 * @var integer the gender type male for a speaker
	 */
	const GENDER_MALE = 1;

	/**
	 * @var integer the gender type female for a speaker
	 */
	const GENDER_FEMALE = 2;

	/**
	 * Returns our name.
	 *
	 * @return string our name, will not be empty
	 *
	 * @see EXT:oelib/Interface/tx_oelib_Interface_MailRole#getName()
	 */
	public function getName() {
		return $this->getAsString('title');
	}

	/**
	 * Sets our name.
	 *
	 * @param string our name to set, must not be empty
	 */
	public function setName($name) {
		if ($name == '') {
			throw new Exception('The parameter $name must not be empty.');
		}

		$this->setAsString('title', $name);
	}

	/**
	 * Returns our organization.
	 *
	 * @return string our organization, will be empty if there's no organization
	 *                set
	 */
	public function getOrganization() {
		return $this->getAsString('organization');
	}

	/**
	 * Sets our organization.
	 *
	 * @param string our organization, may be empty
	 */
	public function setOrganization($organization) {
		$this->setAsString('organization', $organization);
	}

	/**
	 * Returns whether this speaker has an organization.
	 *
	 * @return boolean TRUE if this speaker has an organization, FALSE otherwise
	 */
	public function hasOrganization() {
		return $this->hasString('organization');
	}

	/**
	 * Returns our homepage.
	 *
	 * @return string our homepage, will be empty if there's no homepage set
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
	 * Returns whether this speaker has a homepage.
	 *
	 * @return boolean TRUE if this speaker has a homepage, FALSE otherwise
	 */
	public function hasHomepage() {
		return $this->hasString('homepage');
	}

	/**
	 * Returns our description.
	 *
	 * @return string our description, will be empty if there's no description
	 *                set
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
	 * Returns whether this speaker has a description.
	 *
	 * @return boolean TRUE if this speaker has a description, FALSE otherwise
	 */
	public function hasDescription() {
		return $this->hasString('description');
	}

	/**
	 * Returns our skills.
	 *
	 * @return tx_oelib_List our skills, will be empty if there are no skills
	 *                       related to this speaker
	 */
	public function getSkills() {
		return $this->getAsList('skills');
	}

	/**
	 * Sets this speaker's skills.
	 *
	 * @param tx_oelib_List $skills this speaker's skills, may be empty
	 */
	public function setSkills(tx_oelib_List $skills) {
		$this->set('skills', $skills);
	}

	/**
	 * Returns our address.
	 *
	 * @return string our address, will be empty if there's no address set
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
	 * @return boolean TRUE if this address has an address, FALSE otherwise
	 */
	public function hasAddress() {
		return $this->hasString('address');
	}

	/**
	 * Returns our work telephone number.
	 *
	 * @return string our work telephone number, will be empty if there's no
	 *                work telephone number set
	 */
	public function getPhoneWork() {
		return $this->getAsString('phone_work');
	}

	/**
	 * Sets our work telephone number.
	 *
	 * @param string our work telephone number to set, may be empty
	 */
	public function setPhoneWork($phoneWork) {
		$this->setAsString('phone_work', $phoneWork);
	}

	/**
	 * Returns whether this speaker has a work telephone number.
	 *
	 * @return boolean TRUE if this speaker has a work telephone number, FALSE
	 *                 otherwise
	 */
	public function hasPhoneWork() {
		return $this->hasString('phone_work');
	}

	/**
	 * Returns our home telephone number.
	 *
	 * @return string our home telephone number, will be empty if there's no
	 *                home telephone number set
	 */
	public function getPhoneHome() {
		return $this->getAsString('phone_home');
	}

	/**
	 * Sets our home telephone number.
	 *
	 * @param string our home telephone number to set, may be empty
	 */
	public function setPhoneHome($phoneHome) {
		$this->setAsString('phone_home', $phoneHome);
	}

	/**
	 * Returns whether this speaker has a home telephone number.
	 *
	 * @return boolean TRUE if this speaker has a home telephone number, FALSE
	 *                 otherwise
	 */
	public function hasPhoneHome() {
		return $this->hasString('phone_home');
	}

	/**
	 * Returns our mobile telephone number.
	 *
	 * @return string our mobile telephone number, will be empty if there's no
	 *                mobile telephone number set
	 */
	public function getPhoneMobile() {
		return $this->getAsString('phone_mobile');
	}

	/**
	 * Sets our mobile telephone number.
	 *
	 * @param string our mobile telephone number to set, may be empty
	 */
	public function setPhoneMobile($phoneMobile) {
		$this->setAsString('phone_mobile', $phoneMobile);
	}

	/**
	 * Returns whether this speaker has a mobile telephone number.
	 *
	 * @return boolean TRUE if this speaker has a mobile telephone number, FALSE
	 *                 otherwise
	 */
	public function hasPhoneMobile() {
		return $this->hasString('phone_mobile');
	}

	/**
	 * Returns our fax number.
	 *
	 * @return string our fax number, will be empty if there's no fax number set
	 */
	public function getFax() {
		return $this->getAsString('fax');
	}

	/**
	 * Sets our fax number.
	 *
	 * @param string our fax number to set, may be empty
	 */
	public function setFax($fax) {
		$this->setAsString('fax', $fax);
	}

	/**
	 * Returns whether this speaker has a fax number.
	 *
	 * @return boolean TRUE if this speaker has a fax number, FALSE otherwise
	 */
	public function hasFax() {
		return $this->hasString('fax');
	}

	/**
	 * Returns our e-mail address.
	 *
	 * @return string our e-mail address, will not be empty
	 *
	 * @see EXT:oelib/Interface/tx_oelib_Interface_MailRole#getEMailAddress()
	 */
	public function getEMailAddress() {
		return $this->getAsString('email');
	}

	/**
	 * Sets out e-mail address.
	 *
	 * @param string our e-mail address, may be empty
	 */
	public function setEMailAddress($eMailAddress) {
		$this->setAsString('email', $eMailAddress);
	}

	/**
	 * Returns whether this speaker has an e-mail address.
	 *
	 * @return boolean TRUE if this speaker has an e-mail address, FALSE
	 *                 otherwise
	 */
	public function hasEMailAddress() {
		return $this->hasString('email');
	}

	/**
	 * Returns our gender.
	 *
	 * @return integer our gender, will be either GENDER_MALE, GENDER_FEMALE or
	 *                 GENDER_UNKNOWN if the speaker has no gender
	 */
	public function getGender() {
		return $this->getAsInteger('gender');
	}

	/**
	 * Sets our gender.
	 *
	 * @param integer our gender to set, must be one of
	 *                tx_seminars_Model_Speaker::GENDER_FEMALE,
	 *                tx_seminars_Model_Speaker::GENDER_MALE
	 *                or tx_seminars_Model_Speaker::GENDER_UNKNOWN
	 */
	public function setGender($gender) {
		$this->setAsInteger('gender', $gender);
	}

	/**
	 * Returns whether this speaker has a gender.
	 *
	 * @return boolean TRUE if this speaker has a gender, FALSE otherwise
	 */
	public function hasGender() {
		return $this->hasInteger('gender');
	}

	/**
	 * Returns our cancelation period in days.
	 *
	 * @return integer our cancelation period in days, will be >= 0
	 */
	public function getCancelationPeriod() {
		return $this->getAsInteger('cancelation_period');
	}

	/**
	 * Sets our cancelation period in days.
	 *
	 * @param integer our cancelation period in days to set, must be >= 0
	 */
	public function setCancelationPeriod($cancelationPeriod) {
		if ($cancelationPeriod < 0) {
			throw new Exception('The parameter $cancelationPeriod must be >= 0.');
		}

		$this->setAsInteger('cancelation_period', $cancelationPeriod);
	}

	/**
	 * Returns whether this speaker has a cancelation period set.
	 *
	 * @return boolean TRUE if this speaker has a cancelation period set, FALSE
	 *                 otherwise
	 */
	public function hasCancelationPeriod() {
		return $this->hasInteger('cancelation_period');
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/Speaker.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/Speaker.php']);
}
?>