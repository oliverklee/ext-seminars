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
 */
class ext_update {
	/**
	 * Returns the update module content.
	 *
	 * @return string the update module content, will not be empty
	 */
	public function main() {
		return $this->updateEventOrganizerRelations();
	}

	/**
	 * Returns whether the update module may be accessed.
	 *
	 * @return boolean true if the update module may be accessed, false otherwise
	 */
	public function access() {
		if (!t3lib_extMgm::isLoaded('seminars')) {
			return false;
		}

		return $this->needsToUpdateEventOrganizerRelations()
			&& $this->hasEventsWithOrganizers();
	}

	/**
	 * Updates the event-organizer-relations to real M:M relations.
	 *
	 * @return string information about the status of the update process,
	 *                will not be empty.
	 */
	private function updateEventOrganizerRelations() {
		$result = '<h2>Updating event-organizer-relations:</h2>';
		$result .= '<ul>';
		// Gets all events which have an organizer set.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, title, organizers',
			SEMINARS_TABLE_SEMINARS,
			SEMINARS_TABLE_SEMINARS . '.organizers<>0'
		);

		if (!$dbResult) {
			throw new Exception(DATABASE_RESULT_ERROR);
		}

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			$result .= '<li>Event #' . $row['uid'];

			// Adds a relation entry for each organizer UID.
			$result .= '<ul>';
			$sorting = 0;
			$organizerUids = t3lib_div::trimExplode(',', $row['organizers'], true);
			foreach ($organizerUids as $organizerUid) {
				$result .= '<li>Organizer #' . $organizerUid . '</li>';
				$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					SEMINARS_TABLE_SEMINARS_ORGANIZERS_MM,
					array(
						'uid_local' => $row['uid'],
						'uid_foreign' => intval($organizerUid),
						'sorting' => $sorting,
					)
				);
				$sorting++;
			}
			$result .= '</ul>';

			// Updates the event's organizers field with the number of organizer
			// UIDs.
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				SEMINARS_TABLE_SEMINARS,
				SEMINARS_TABLE_SEMINARS . '.uid=' . $row['uid'],
				array('organizers' => count($organizerUids))
			);

			$result .= '</li>';
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		$result .= '</ul>';

		return $result;
	}

	/**
	 * Checks whether there are no real event-organizer-m:n-relations yet.
	 *
	 * @return boolean true if there are no real event-organizer-m:n-relations,
	 *                 false otherwise
	 */
	private function needsToUpdateEventOrganizerRelations() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS count', SEMINARS_TABLE_SEMINARS_ORGANIZERS_MM, '1=1'
		);

		if (!$dbResult) {
			throw new Exception(DATABASE_RESULT_ERROR);
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);

		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		return ($row['count'] == 0);
	}

	/**
	 * Checks whether there are any events with organizers set.
	 *
	 * @return boolean true if there is at least one event with organizers set,
	 *                 false otherwise
	 */
	private function hasEventsWithOrganizers() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS count',
			SEMINARS_TABLE_SEMINARS,
			SEMINARS_TABLE_SEMINARS . '.organizers<>0'
		);

		if (!$dbResult) {
			throw new Exception(DATABASE_RESULT_ERROR);
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);

		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		return ($row['count'] > 0);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.ext_update.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.ext_update.php']);
}
?>