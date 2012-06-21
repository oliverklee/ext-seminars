<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Niels Pardon (mail@niels-pardon.de)
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

if (t3lib_extMgm::isLoaded('oelib')) {
	require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
}

/**
 * Class 'ext_update' for the 'seminars' extension.
 *
 * This class offers functions to update the database from one version to
 * another.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ext_update {
	/**
	 * Returns the update module content.
	 *
	 * @return string the update module content, will be empty if nothing was
	 *                updated
	 */
	public function main() {
		$result = '';
		try {
			if ($this->needsToUpdateEventField('registrations')) {
				$result .= $this->updateRegistrationsField();
			}
		} catch (tx_oelib_Exception_Database $exception) {
			$result = '';
		}

		return $result;
	}

	/**
	 * Returns whether the update module may be accessed.
	 *
	 * @return boolean TRUE if the update module may be accessed, FALSE otherwise
	 */
	public function access() {
		if (!t3lib_extMgm::isLoaded('oelib')
			|| !t3lib_extMgm::isLoaded('seminars')
		) {
			return FALSE;
		}
		if (!tx_oelib_db::existsTable('tx_seminars_seminars')
			|| !tx_oelib_db::existsTable('tx_seminars_attendances')
		) {
			return FALSE;
		}
		if (!tx_oelib_db::tableHasColumn(
			'tx_seminars_seminars', 'registrations'
		)) {
			return FALSE;
		}

		try {
			$result = $this->needsToUpdateEventField('registrations');
		} catch (tx_oelib_Exception_Database $exception) {
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Checks whether there are events which need to be updated concerning a
	 * given DB field.
	 *
	 * @param string $fieldToUpdate the DB field to check for needing an update, must be 'registrations'
	 *
	 * @return boolean TRUE if any rows need to be updated, FALSE otherwise
	 */
	private function needsToUpdateEventField($fieldToUpdate) {
		switch ($fieldToUpdate) {
			case 'registrations':
				$whereClause = 'registrations = 0 AND EXISTS (' .
					'SELECT * FROM tx_seminars_attendances' .
					' WHERE seminar = tx_seminars_seminars.uid ' .
					tx_oelib_db::enableFields('tx_seminars_attendances') .
					')';
				break;
			default:
				throw new InvalidArgumentException(
					'needsToUpdateEventField was called with "' . $fieldToUpdate .
						'", but the allowed value is only "registrations"',
					1333291685
				);
				break;
		}

		return (tx_oelib_db::count('tx_seminars_seminars', $whereClause) > 0);
	}

	/**
	 * Updates the "registrations" field of the event records.
	 *
	 * @return string information about the status of the update process,
	 *                will not be empty
	 */
	private function updateRegistrationsField() {
		$query = 'UPDATE tx_seminars_seminars SET registrations = ' .
				'(SELECT COUNT(*) FROM tx_seminars_attendances' .
				' WHERE seminar = tx_seminars_seminars.uid ' .
				tx_oelib_db::enableFields('tx_seminars_attendances') . ')' .
			' WHERE registrations = 0 AND EXISTS (' .
				'SELECT * FROM tx_seminars_attendances' .
				' WHERE seminar = tx_seminars_seminars.uid ' .
				tx_oelib_db::enableFields('tx_seminars_attendances') .
			')';
		if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
			throw new tx_oelib_Exception_Database();
		}

		return '<h2>Updating the events.registrations field.</h2>' .
			'<p>Updating ' . $GLOBALS['TYPO3_DB']->sql_affected_rows() .
			' event records.</p>';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.ext_update.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.ext_update.php']);
}
?>