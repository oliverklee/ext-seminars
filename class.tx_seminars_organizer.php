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
 * Class 'tx_seminars_organizer' for the 'seminars' extension.
 *
 * This class represents an organizer.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_organizer extends tx_seminars_objectfromdb {
	/** string with the name of the SQL table this class corresponds to */
	protected $tableName = SEMINARS_TABLE_ORGANIZERS;

	/**
	 * Gets our homepage.
	 *
	 * @return string our homepage (or '' if there is an error)
	 */
	public function getHomepage() {
		return $this->getRecordPropertyString('homepage');
	}

	/**
	 * Returns true if this organizer has a homepage set, false otherwise.
	 *
	 * @return boolean true if this organizer has a homepage set, false
	 *                 otherwise
	 */
	public function hasHomepage() {
		return $this->hasRecordPropertyString('homepage');
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
	 * @return integer our attendances PID or 0 if there is no attendances
	 *                 PID set
	 */
	public function getAttendancesPid() {
		return $this->getRecordPropertyInteger('attendances_pid');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_organizer.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_organizer.php']);
}
?>