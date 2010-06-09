<?php
/***************************************************************
* Copyright notice
*
* (c) 2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testing version of tx_seminars_BackEndExtJs_Ajax for the "seminars" extension.
 *
 * Fixture for testing purposes. Makes some protected functions public so we can
 * test them.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax extends tx_seminars_BackEndExtJs_Ajax {
	/**
	 * Returns the data of the given event in an array.
	 *
	 * @param tx_seminars_Model_Event $event
	 *        the event to return the data from in an array
	 *
	 * @return array the data of the given in event
	 */
	public function getArrayFromEvent(tx_seminars_Model_Event $event) {
		return parent::getArrayFromEvent($event);
	}

	/**
	 * Returns the data of the given registration in an array.
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to return the data from in an array
	 *
	 * @return array the data of the given registration
	 */
	public function getArrayFromRegistration(tx_seminars_Model_Registration $registration) {
		return parent::getArrayFromRegistration($registration);
	}

	/**
	 * Returns the data of the given speaker in an array.
	 *
	 * @param tx_seminars_Model_Speaker $speaker
	 *        the speaker to return the data from in an array
	 *
	 * @return array the data of the given speaker
	 */
	public function getArrayFromSpeaker(tx_seminars_Model_Speaker $speaker) {
		return parent::getArrayFromSpeaker($speaker);
	}

	/**
	 * Returns the data of the given organizer in an array.
	 *
	 * @param tx_seminars_Model_Organizer $organizer
	 *        the organizer to return the data from in an array
	 *
	 * @return array the data of the given organizer
	 */
	public function getArrayFromOrganizer(tx_seminars_Model_Organizer $organizer) {
		return parent::getArrayFromOrganizer($organizer);
	}

	/**
	 * Retrieves the models for a given mapper name.
	 *
	 * @param string $mapperName
	 *        the name of the mapper to get the models from, must be a valid
	 *        mapper class name, must not be empty
	 * @param TYPO3AJAX $ajaxObject
	 *        the AJAX object used to set the content and content-type of the
	 *        response of the AJAX call
	 *
	 * @return tx_oelib_List will be a list of models in case of success, null
	 *                       in case of failure
	 */
	public function retrieveModels($mapperName, TYPO3AJAX $ajaxObject) {
		return parent::retrieveModels($mapperName, $ajaxObject);
	}

	/**
	 * Checks whether the given page UID is a valid system folder.
	 *
	 * @param integer $pageUid the page UID to check, may be 0 or negative
	 *
	 * @return boolean TRUE if the given page UID is a valid system folder,
	 *                 FALSE otherwise
	 */
	public function isPageUidValid($pageUid) {
		return parent::isPageUidValid($pageUid);
	}
}
?>