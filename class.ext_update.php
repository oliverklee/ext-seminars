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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * This class offers functions to update the database from one version to another.
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
	 * @return bool TRUE if the update module may be accessed, FALSE otherwise
	 */
	public function access() {
		if (!ExtensionManagementUtility::isLoaded('oelib')
			|| !ExtensionManagementUtility::isLoaded('seminars')
		) {
			return FALSE;
		}
		if (!Tx_Oelib_Db::existsTable('tx_seminars_seminars')
			|| !Tx_Oelib_Db::existsTable('tx_seminars_attendances')
		) {
			return FALSE;
		}
		if (!Tx_Oelib_Db::tableHasColumn(
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
	 * @return bool TRUE if any rows need to be updated, FALSE otherwise
	 */
	private function needsToUpdateEventField($fieldToUpdate) {
		switch ($fieldToUpdate) {
			case 'registrations':
				$whereClause = 'registrations = 0 AND EXISTS (' .
					'SELECT * FROM tx_seminars_attendances' .
					' WHERE seminar = tx_seminars_seminars.uid ' .
					Tx_Oelib_Db::enableFields('tx_seminars_attendances') .
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

		return (Tx_Oelib_Db::count('tx_seminars_seminars', $whereClause) > 0);
	}

	/**
	 * Updates the "registrations" field of the event records.
	 *
	 * @return string information about the status of the update process, will not be empty
	 *
	 * @throws tx_oelib_Exception_Database
	 */
	private function updateRegistrationsField() {
		$query = 'UPDATE tx_seminars_seminars SET registrations = ' .
				'(SELECT COUNT(*) FROM tx_seminars_attendances' .
				' WHERE seminar = tx_seminars_seminars.uid ' .
				Tx_Oelib_Db::enableFields('tx_seminars_attendances') . ')' .
			' WHERE registrations = 0 AND EXISTS (' .
				'SELECT * FROM tx_seminars_attendances' .
				' WHERE seminar = tx_seminars_seminars.uid ' .
				Tx_Oelib_Db::enableFields('tx_seminars_attendances') .
			')';
		if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
			throw new tx_oelib_Exception_Database();
		}

		return '<h2>Updating the events.registrations field.</h2>' .
			'<p>Updating ' . $GLOBALS['TYPO3_DB']->sql_affected_rows() .
			' event records.</p>';
	}
}