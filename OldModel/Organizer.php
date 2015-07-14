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
 * This class represents an organizer.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_OldModel_Organizer extends tx_seminars_OldModel_Abstract implements tx_oelib_Interface_MailRole {
	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = 'tx_seminars_organizers';

	/**
	 * Gets the organizer's real name.
	 *
	 * @return string the organizer's real name, will not be empty for valid records
	 */
	public function getName() {
		return $this->getTitle();
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
	 * Returns TRUE if this organizer has a homepage set, FALSE otherwise.
	 *
	 * @return bool TRUE if this organizer has a homepage set, FALSE
	 *                 otherwise
	 */
	public function hasHomepage() {
		return $this->hasRecordPropertyString('homepage');
	}

	/**
	 * Gets the organizer's e-mail address.
	 *
	 * @return string the organizer's e-mail address, will only be empty if
	 *                there is an error
	 */
	public function getEMailAddress() {
		return $this->getRecordPropertyString('email');
	}

	/**
	 * Gets our e-mail footer.
	 *
	 * @return string our e-mail footer (or '' if there is an error)
	 */
	public function getEmailFooter() {
		return $this->getRecordPropertyString('email_footer');
	}

	/**
	 * Gets our attendances PID, will be 0 if there is no attendances PID set.
	 *
	 * @return int our attendances PID or 0 if there is no attendances
	 *                 PID set
	 */
	public function getAttendancesPid() {
		return $this->getRecordPropertyInteger('attendances_pid');
	}

	/**
	 * Checks whether this organizer has a description.
	 *
	 * @return bool TRUE if this organizer has a description, FALSE otherwise
	 */
	public function hasDescription() {
		return $this->hasRecordPropertyString('description');
	}

	/**
	 * Returns the description of the organizer.
	 *
	 * @return string the description of the organizer in raw format, will be
	 *                empty if organizer has no description
	 */
	public function getDescription() {
		return $this->getRecordPropertyString('description');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/OldModel/Organizer.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/OldModel/Organizer.php']);
}