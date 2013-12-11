<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Niels Pardon (mail@niels-pardon.de)
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
 * This class provides functionality for creating a list of events for usage in an AJAX call.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_RegistrationsList extends tx_seminars_BackEndExtJs_Ajax_AbstractList {
	/**
	 * the class name of the mapper to use to create the list
	 *
	 * @var string
	 */
	protected $mapperName = 'tx_seminars_Mapper_Registration';

	/**
	 * Returns the data of the given registration in an array.
	 *
	 * Available array keys are: title
	 *
	 * @param tx_oelib_Model $registration
	 *        the registration to return the data from in an array
	 *
	 * @return array the data of the given registration with the name of the
	 *               field as the key
	 */
	protected function getAdditionalFields(tx_oelib_Model $registration) {
		$result = array();

		try {
			$result['name'] = htmlspecialchars($registration->getFrontEndUser()->getName());
		} catch (Exception $exception) {
			$result['name'] = '';
		}

		try {
			$result['event_accreditation_number'] = htmlspecialchars($registration->getEvent()->getAccreditationNumber());
			$result['event_title'] = htmlspecialchars($registration->getEvent()->getTitle());
			$result['event_begin_date'] = date('r', $registration->getEvent()->getBeginDateAsUnixTimeStamp());
			$result['event_end_date'] = date('r', $registration->getEvent()->getEndDateAsUnixTimeStamp());
		} catch (Exception $exception) {
			$result['event_accreditation_number'] = '';
			$result['event_title'] = '';
			$result['event_begin_date'] = '';
			$result['event_end_date'] = '';
		}

		return $result;
	}

	/**
	 * Returns whether the currently logged in back-end user is allowed to view
	 * the list.
	 *
	 * @return boolean TRUE if the currently logged in back-end user is allowed
	 *                 to view the list, FALSE otherwise
	 */
	protected function hasAccess() {
		return $GLOBALS['BE_USER']->check(
			'tables_select', 'tx_seminars_attendances'
		);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/RegistrationsList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/RegistrationsList.php']);
}