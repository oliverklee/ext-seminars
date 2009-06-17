<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'tx_seminars_speaker' for the 'seminars' extension.
 *
 * This class represents a speaker.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_speaker extends tx_seminars_objectfromdb {
	/** @var integer the gender type for speakers without gender */
	const GENDER_UNKNOWN = 0;

	/** @var integer the gender type male for a speaker */
	const GENDER_MALE = 1;

	/** @var integer the gender type female for a speaker */
	const GENDER_FEMALE = 2;

	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = SEMINARS_TABLE_SPEAKERS;

	/**
	 * Gets our organization.
	 *
	 * @return string our organization (or '' if there is an error)
	 */
	public function getOrganization() {
		return $this->getRecordPropertyString('organization');
	}

	/**
	 * Returns true if this speaker has an organization, false otherwise.
	 *
	 * @return boolean true if this speaker has an organization, false otherwise
	 */
	public function hasOrganization() {
		return $this->hasRecordPropertyString('organization');
	}

	/**
	 * Gets our homepage.
	 *
	 * @return string our homepage (or '' if there is an error)
	 */
	public function getHomepage() {
		return $this->getRecordPropertyString('homepage');
	}

	/**
	 * Returns true if this speaker has a homepage, false otherwise.
	 *
	 * @return boolean true if this speaker has a homepage, false otherwise
	 */
	public function hasHomepage() {
		return $this->hasRecordPropertyString('homepage');
	}

	/**
	 * Gets our description.
	 *
	 * @param tslib_pibase the live pibase object
	 *
	 * @return string our description (or '' if there is an error)
	 */
	public function getDescription(tslib_pibase $plugin) {
		return $plugin->pi_RTEcssText(
			$this->getRecordPropertyString('description')
		);
	}

	/**
	 * Gets our description without RTE processing.
	 *
	 * @return string our description (or '' if there is an error)
	 */
	public function getDescriptionRaw() {
		return $this->getRecordPropertyString('description');
	}

	/**
	 * Returns true if this speaker has a description, false otherwise.
	 *
	 * @return boolean true if this speaker has a description, false otherwise
	 */
	public function hasDescription() {
		return $this->hasRecordPropertyString('description');
	}

	/**
	 * Checks whether we have any skills set.
	 *
	 * @return boolean true if we have any skills related to this speaker,
	 *                 false otherwise
	 */
	public function hasSkills() {
		return $this->hasRecordPropertyInteger('skills');
	}

	/**
	 * Gets our skills as a plain text list (just the skill names).
	 *
	 * @return string our skills list (or an empty string if there are no
	 *                skills for this speaker or there is an error)
	 */
	public function getSkillsShort() {
		if (!$this->hasSkills()) {
			return '';
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			SEMINARS_TABLE_SKILLS.', '.SEMINARS_TABLE_SPEAKERS_SKILLS_MM,
			'uid_local=' . $this->getUid() . ' AND uid=uid_foreign' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_SKILLS),
			'',
			'sorting ASC'
		);

		if (!$dbResult) {
			return '';
		}

		$result = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			$result[] = $row['title'];
		}

		return implode(', ', $result);
	}

	/**
	 * Gets the number of skills associated with this speaker.
	 *
	 * @return integer the number of skills associated with this speaker,
	 *                 will be >= 0
	 */
	public function getNumberOfSkills() {
		return $this->getRecordPropertyInteger('skills');
	}

	/**
	 * Gets our internal notes.
	 *
	 * @return string our internal notes (or '' if there is an error)
	 */
	public function getNotes() {
		return $this->getRecordPropertyString('notes');
	}

	/**
	 * Gets our address.
	 *
	 * @return string our address (or '' if there is an error)
	 */
	public function getAddress() {
		return $this->getRecordPropertyString('address');
	}

	/**
	 * Gets our work phone number.
	 *
	 * @return string our work phone number (or '' if there is an error)
	 */
	public function getPhoneWork() {
		return $this->getRecordPropertyString('phone_work');
	}

	/**
	 * Gets our home phone number.
	 *
	 * @return string our home phone number (or '' if there is an error)
	 */
	public function getPhoneHome() {
		return $this->getRecordPropertyString('phone_home');
	}

	/**
	 * Gets our mobile phone number.
	 *
	 * @return string our mobile phone number (or '' if there is an error)
	 */
	public function getPhoneMobile() {
		return $this->getRecordPropertyString('phone_mobile');
	}

	/**
	 * Gets our fax number.
	 *
	 * @return string our fax number (or '' if there is an error)
	 */
	public function getFax() {
		return $this->getRecordPropertyString('fax');
	}

	/**
	 * Gets our e-mail address.
	 *
	 * @return string our e-mail address (or '' if there is an error)
	 */
	public function getEmail() {
		return $this->getRecordPropertyString('email');
	}

	/**
	 * Creates a link to this speaker's homepage, with the title as link text.
	 *
	 * @param tx_oelib_templatehelper templatehelper object with current
	 *                                configuration values
	 *
	 * @return string this speaker's title wrapped in an link tag, or if the
	 *                speaker has no homepage just the speaker name, will not
	 *                be empty
	 */
	public function getLinkedTitle(tx_oelib_templatehelper $plugin) {
		if ($this->hasHomepage()) {
			$result = $plugin->cObj->getTypoLink(
				$this->getTitle(),
				$this->getHomepage(),
				array(),
				$plugin->getConfValueString('externalLinkTarget')
			);
		} else {
			$result = $this->getTitle();
		}

		return $result;
	}

	/**
	 * Returns the gender of this speaker.
	 *
	 * @return integer the gender of the speaker, will be either
	 *                 GENDER_MALE,
	 *                 GENDER_FEMALE or
	 *                 GENDER_UNKNOWN if the speaker has no gender
	 */
	public function getGender() {
		return $this->getRecordPropertyInteger('gender');
	}

	/**
	 * Sets the gender of this speaker.
	 *
	 * @param integer the gender of the speaker, must be one of
	 *                tx_seminars_speaker::GENDER_FEMALE,
	 *                tx_seminars_speaker::GENDER_MALE
	 *                or tx_seminars_speaker::GENDER_UNKNOWN
	 */
	public function setGender($gender) {
		$this->setRecordPropertyInteger('gender', $gender);
	}

	/**
	 * Returns true if this speaker has a cancelation period.
	 *
	 * @return boolean true if the speaker has a cancelation period, false
	 *                 otherwise
	 */
	public function hasCancelationPeriod() {
		return $this->hasRecordPropertyInteger('cancelation_period');
	}

	/**
	 * Returns the cancelation period of this speaker in days.
	 *
	 * @return integer the cancelation period in days, will be >= 0
	 */
	public function getCancelationPeriodInDays() {
		return $this->getRecordPropertyInteger('cancelation_period');
	}

	/**
	 * Sets the gender cancelation period of this speaker
	 *
	 * @param integer the cancelation period of this speaker in days,
	 *                must be > 0
	 */
	public function setCancelationPeriod($cancelationPeriod) {
		$this->setRecordPropertyInteger('cancelation_period', $cancelationPeriod);
	}

	/**
	 * Returns our owner.
	 *
	 * @return tx_seminars_Model_FrontEndUser the owner of this model, will be null
	 *                                     if this model has no owner
	 */
	public function getOwner() {
		if (!$this->hasRecordPropertyInteger('owner')) {
			return null;
		}

		return tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUser'
		)->find($this->getRecordPropertyInteger('owner'));
	}

	/**
	 * Sets our owner.
	 *
	 * @param tx_seminars_Model_FrontEndUser $frontEndUser the owner of this model
	 *                                                  to set
	 */
	public function setOwner(tx_seminars_Model_FrontEndUser $frontEndUser) {
		$this->setRecordPropertyInteger('owner', $frontEndUser->getUid());
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_speaker.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_speaker.php']);
}
?>