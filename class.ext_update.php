<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
if (t3lib_extMgm::isLoaded('seminars')) {
	require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
}

/**
 * Class 'ext_update' for the 'seminars' extension.
 *
 * This class offers functions to update the database from one version to another.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
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
			if ($this->needsToUpdateEventField('needsRegistration')) {
				$result .= $this->updateNeedsRegistrationField();
			}
			if ($this->needsToUpdateEventField('queueSize')) {
				$result .= $this->updateQueueSizeField();
			}
			if ($this->needsToUpdateTypoScriptTemplates()) {
				$result .= $this->updateTypoScriptTemplates();
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
		if (!tx_oelib_db::existsTable(SEMINARS_TABLE_SEMINARS)) {
			return FALSE;
		}
		if (!tx_oelib_db::tableHasColumn(
			SEMINARS_TABLE_SEMINARS, 'needs_registration'
		)) {
			return FALSE;
		}

		try {
			$result = ($this->needsToUpdateEventField('needsRegistration')
				|| $this->needsToUpdateEventField('queueSize')
				|| $this->needsToUpdateTypoScriptTemplates()
			);
		} catch (tx_oelib_Exception_Database $exception) {
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Checks whether there are events which need to be updated, in given DB
	 * field.
	 *
	 * @param string the DB field to check for needing an update, must be
	 *               'queueSize' or 'needsRegistration
	 *
	 * @return boolean TRUE if any rows need to be updated, FALSE otherwise
	 */
	private function needsToUpdateEventField($fieldToUpdate) {
		switch ($fieldToUpdate) {
			case 'needsRegistration':
				$whereClause = 'attendees_max > 0 AND needs_registration = 0 ';
				break;
			case 'queueSize':
				$whereClause = 'queue_size > 1';
				break;
			default:
				throw new InvalidArgumentException(
					'needsToUpdateEventField was called with "' . $fieldToUpdate .
						'", but allowed values are only '\'needsRegistration\' and \'queueSize\'.',
					1333291685
				);
		}

		$row = tx_oelib_db::selectSingle(
			'COUNT(*) AS number', SEMINARS_TABLE_SEMINARS, $whereClause
		);

		return ($row['number'] > 0);
	}

	/**
	 * Updates the needs_registration field of the event records.
	 *
	 * @return string information about the status of the update process,
	 *                will not be empty
	 */
	private function updateNeedsRegistrationField() {
		return '<h2>Updating events needs_registrations field.</h2>' .
			'<p> Updating ' . tx_oelib_db::update(
				SEMINARS_TABLE_SEMINARS,
				'needs_registration = 0 AND attendees_max > 0',
				array('needs_registration' => 1)
			) . ' events.</p>';
	}

	/**
	 * Updates the queue_size field of the event records, changing it from
	 * integer to boolean.
	 *
	 * @return string information about the status of the update process,
	 *                will not be empty
	 */
	private function updateQueueSizeField() {
		return '<h2>Updating events queue_size field.</h2>' .
			'<p> Updating ' . tx_oelib_db::update(
				SEMINARS_TABLE_SEMINARS,
				'queue_size > 1',
				array('queue_size' => 1)
			) . ' events.</p>';
	}

	/**
	 * Returns whether there are any TypoScript template records that need to be
	 * updated.
	 *
	 * @return boolean TRUE if there are any TypoScript template records that
	 *                 need to be updated, FALSE otherwise
	 */
	private function needsToUpdateTypoScriptTemplates() {
		$row = tx_oelib_db::selectSingle(
			'COUNT(*) as count', 'sys_template',
			'include_static_file LIKE \'%EXT:seminars/static/%\''
		);

		return ($row['count'] > 0);
	}

	/**
	 * Updates the TypoScript template records.
	 *
	 * @return string information about the status of the update process,
	 *                will not be empty
	 */
	private function updateTypoScriptTemplates() {
		$result = '<h2>Updating TypoScript template records.</h2>';
		$result .= '<ul>';

		$rows = tx_oelib_db::selectMultiple(
			'uid, include_static_file', 'sys_template',
			'include_static_file LIKE \'%EXT:seminars/static/%\''
		);

		foreach ($rows as $row) {
			$result .= '<li>Updating TypoScript template #' . $row['uid'] . '</li>';
			tx_oelib_db::update(
				'sys_template', 'uid=' . $row['uid'],
				array(
					'include_static_file' => str_replace(
						'EXT:seminars/static/',
						'EXT:seminars/Configuration/TypoScript',
						$row['include_static_file']
					),
				)
			);
		}

		$result .= '</ul>';

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.ext_update.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.ext_update.php']);
}
?>