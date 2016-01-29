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
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This class represents a speaker.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_OldModel_Speaker extends Tx_Seminars_OldModel_Abstract {
	/** @var int the gender type for speakers without gender */
	const GENDER_UNKNOWN = 0;

	/** @var int the gender type male for a speaker */
	const GENDER_MALE = 1;

	/** @var int the gender type female for a speaker */
	const GENDER_FEMALE = 2;

	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = 'tx_seminars_speakers';

	/**
	 * Gets our organization.
	 *
	 * @return string our organization (or '' if there is an error)
	 */
	public function getOrganization() {
		return $this->getRecordPropertyString('organization');
	}

	/**
	 * Returns TRUE if this speaker has an organization, FALSE otherwise.
	 *
	 * @return bool TRUE if this speaker has an organization, FALSE otherwise
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
	 * Returns TRUE if this speaker has a homepage, FALSE otherwise.
	 *
	 * @return bool TRUE if this speaker has a homepage, FALSE otherwise
	 */
	public function hasHomepage() {
		return $this->hasRecordPropertyString('homepage');
	}

	/**
	 * Gets our description.
	 *
	 * @param AbstractPlugin $plugin
	 *
	 * @return string our description (or '' if there is an error)
	 */
	public function getDescription(AbstractPlugin $plugin) {
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
	 * Returns TRUE if this speaker has a description, FALSE otherwise.
	 *
	 * @return bool TRUE if this speaker has a description, FALSE otherwise
	 */
	public function hasDescription() {
		return $this->hasRecordPropertyString('description');
	}

	/**
	 * Checks whether we have any skills set.
	 *
	 * @return bool TRUE if we have any skills related to this speaker,
	 *                 FALSE otherwise
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
			'tx_seminars_skills, tx_seminars_speakers_skills_mm',
			'uid_local = ' . $this->getUid() . ' AND uid = uid_foreign' .
				Tx_Oelib_Db::enableFields('tx_seminars_skills'),
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
	 * @return int the number of skills associated with this speaker,
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
	 * @param Tx_Oelib_TemplateHelper $plugin templatehelper object with current configuration values
	 *
	 * @return string this speaker's title wrapped in an link tag, or if the
	 *                speaker has no homepage just the speaker name, will not
	 *                be empty
	 */
	public function getLinkedTitle(Tx_Oelib_TemplateHelper $plugin) {
		$safeTitle = htmlspecialchars($this->getTitle());

		if ($this->hasHomepage()) {
			$result = $plugin->cObj->getTypoLink(
				$safeTitle,
				$this->getHomepage(),
				array(),
				$plugin->getConfValueString('externalLinkTarget')
			);
		} else {
			$result = $safeTitle;
		}

		return $result;
	}

	/**
	 * Returns the gender of this speaker.
	 *
	 * @return int the gender of the speaker, will be either
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
	 * @param int $gender
	 *        the gender of the speaker, must be one of Tx_Seminars_OldModel_Speaker::GENDER_FEMALE, Tx_Seminars_OldModel_Speaker::GENDER_MALE
	 *        or Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN
	 *
	 * @return void
	 */
	public function setGender($gender) {
		$this->setRecordPropertyInteger('gender', $gender);
	}

	/**
	 * Returns TRUE if this speaker has a cancelation period.
	 *
	 * @return bool TRUE if the speaker has a cancelation period, FALSE
	 *                 otherwise
	 */
	public function hasCancelationPeriod() {
		return $this->hasRecordPropertyInteger('cancelation_period');
	}

	/**
	 * Returns the cancelation period of this speaker in days.
	 *
	 * @return int the cancelation period in days, will be >= 0
	 */
	public function getCancelationPeriodInDays() {
		return $this->getRecordPropertyInteger('cancelation_period');
	}

	/**
	 * Sets the gender cancelation period of this speaker
	 *
	 * @param int $cancelationPeriod the cancelation period of this speaker in days, must be > 0
	 *
	 * @return void
	 */
	public function setCancelationPeriod($cancelationPeriod) {
		$this->setRecordPropertyInteger('cancelation_period', $cancelationPeriod);
	}

	/**
	 * Returns our owner.
	 *
	 * @return Tx_Seminars_Model_FrontEndUser the owner of this model, will be null
	 *                                     if this model has no owner
	 */
	public function getOwner() {
		if (!$this->hasRecordPropertyInteger('owner')) {
			return NULL;
		}

		/** @var Tx_Seminars_Mapper_FrontEndUser $mapper */
		$mapper = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUser::class);
		return $mapper->find($this->getRecordPropertyInteger('owner'));
	}

	/**
	 * Sets our owner.
	 *
	 * @param Tx_Seminars_Model_FrontEndUser $frontEndUser the owner of this model to set
	 *
	 * @return void
	 */
	public function setOwner(Tx_Seminars_Model_FrontEndUser $frontEndUser) {
		$this->setRecordPropertyInteger('owner', $frontEndUser->getUid());
	}

	/**
	 * Returns TRUE if the speaker is hidden, otherwise FALSE.
	 *
	 * @return bool TRUE if the speaker is hidden, FALSE otherwise
	 */
	public function isHidden() {
		return $this->getRecordPropertyBoolean('hidden');
	}
}