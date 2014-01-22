<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Oliver Klee (typo3-coding@oliverklee.de)
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
 * This class creates a CSV export of registrations for download.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Csv_DownloadRegistrationListView extends Tx_Seminars_Csv_AbstractRegistrationListView {
	/**
	 * Returns the keys of the front-end user fields to export.
	 *
	 * @return array<string>
	 */
	protected function getFrontEndUserFieldKeys() {
		return $this->configuration->getAsTrimmedArray('fieldsFromFeUserForCsv');
	}

	/**
	 * Returns the keys of the registration fields to export.
	 *
	 * @return array<string>
	 */
	protected function getRegistrationFieldKeys() {
		return $this->configuration->getAsTrimmedArray('fieldsFromAttendanceForCsv');
	}

	/**
	 * Checks whether the export should also contain registrations that are on the queue.
	 *
	 * @return boolean
	 */
	protected function shouldAlsoContainRegistrationsOnQueue() {
		return $this->configuration->getAsBoolean('showAttendancesOnRegistrationQueueInCSV');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Csv/DownloadRegistrationListView.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Csv/DownloadRegistrationListView.php']);
}