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
 * Class tx_seminars_BackEndExtJs_Ajax for the "seminars" extension.
 *
 * This class is called by the ExtJS back-end module via AJAX using ajax.php.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax {
	/**
	 * Generates a list of events and adds it as content to the given AJAX
	 * object which returns it as JSON.
	 *
	 * @param array $parameters the parameters passed by the AJAX call
	 * @param TYPO3AJAX $ajaxObject
	 *        the AJAX object used to set the content and content-type of the
	 *        response of the AJAX call
	 */
	public function getEvents(array $parameters, TYPO3AJAX $ajaxObject) {
		$events = $this->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject);

		if ($events === null) {
			return;
		}

		$rows = array();
		foreach ($events as $event) {
			$rows[] = $this->getArrayFromEvent($event);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
	}

	/**
	 * Returns the data of the given event in an array.
	 *
	 * Available array keys are: uid, record_type, hidden, status, title,
	 * begin_date, end_date
	 *
	 * @param tx_seminars_Model_Event $event
	 *        the event to return the data from in an array
	 *
	 * @return array the data of the given in event
	 */
	protected function getArrayFromEvent(tx_seminars_Model_Event $event) {
		return array(
			'uid' => $event->getUid(),
			'record_type' => $event->getRecordType(),
			'hidden' => $event->isHidden(),
			'status' => $event->getStatus(),
			'title' => $event->getTitle(),
			'begin_date' => $event->getBeginDateAsUnixTimeStamp(),
			'end_date' => $event->getEndDateAsUnixTimeStamp(),
		);
	}

	/**
	 * Generates a list of registrations and adds it as content to the given
	 * AJAX object which returns it as JSON.
	 *
	 * @param array $parameters the parameters passed by the AJAX call
	 * @param TYPO3AJAX $ajaxObject
	 *        the AJAX object used to set the content and content-type of the
	 *        response of the AJAX call
	 */
	public function getRegistrations(array $parameters, TYPO3AJAX $ajaxObject) {
		$registrations = $this->retrieveModels('tx_seminars_Mapper_Registration', $ajaxObject);

		if ($registrations === null) {
			return;
		}

		$rows = array();
		foreach ($registrations as $registration) {
			$rows[] = $this->getArrayFromRegistration($registration);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
	}

	/**
	 * Returns the data of the given registration in an array.
	 *
	 * Available array keys are: uid, title
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to return the data from in an array
	 *
	 * @return array the data of the given registration
	 */
	protected function getArrayFromRegistration(tx_seminars_Model_Registration $registration) {
		return array(
			'uid' => $registration->getUid(),
			'title' => $registration->getTitle(),
		);
	}

	/**
	 * Generates a list of speakers and adds it as content to the given AJAX
	 * object which returns it as JSON.
	 *
	 * @param array $parameters the parameters passed by the AJAX call
	 * @param TYPO3AJAX $ajaxObject
	 *        the AJAX object used to set the content and content-type of the
	 *        response of the AJAX call
	 */
	public function getSpeakers(array $parameters, TYPO3AJAX $ajaxObject) {
		$speakers = $this->retrieveModels('tx_seminars_Mapper_Speaker', $ajaxObject);

		if ($speakers === null) {
			return;
		}

		$rows = array();
		foreach ($speakers as $speaker) {
			$rows[] = $this->getArrayFromSpeaker($speaker);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
	}

	/**
	 * Returns the data of the given speaker in an array.
	 *
	 * Available array keys are: uid, title
	 *
	 * @param tx_seminars_Model_Speaker $speaker
	 *        the speaker to return the data from in an array
	 *
	 * @return array the data of the given speaker
	 */
	protected function getArrayFromSpeaker(tx_seminars_Model_Speaker $speaker) {
		return array(
			'uid' => $speaker->getUid(),
			'title' => $speaker->getName(),
		);
	}

	/**
	 * Generates a list of organizers and adds it as content to the given AJAX
	 * object which returns it as JSON.
	 *
	 * @param array $parameters the parameters passed by the AJAX call
	 * @param TYPO3AJAX $ajaxObject
	 *        the AJAX object used to set the content and content-type of the
	 *        response of the AJAX call
	 */
	public function getOrganizers(array $parameters, TYPO3AJAX $ajaxObject) {
		$organizers = $this->retrieveModels('tx_seminars_Mapper_Organizer', $ajaxObject);

		if ($organizers === null) {
			return;
		}

		$rows = array();
		foreach ($organizers as $organizer) {
			$rows[] = $this->getArrayFromOrganizer($organizer);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
	}

	/**
	 * Returns the data of the given organizer in an array.
	 *
	 * Available array keys are: uid, title
	 *
	 * @param tx_seminars_Model_Organizer $organizer
	 *        the organizer to return the data from in an array
	 *
	 * @return array the data of the given organizer
	 */
	protected function getArrayFromOrganizer(tx_seminars_Model_Organizer $organizer) {
		return array(
			'uid' => $organizer->getUid(),
			'title' => $organizer->getName(),
		);
	}

	/**
	 * Retrieves the models for a given mapper name.
	 *
	 * @param string $mapperName
	 *        the name of the mapper to get the models from, must be a non-empty
	 *        valid mapper class name
	 * @param TYPO3AJAX $ajaxObject
	 *        the AJAX object used to set the content and content-type of the
	 *        response of the AJAX call
	 *
	 * @return tx_oelib_List will be a list of models in case of success, null
	 *                       in case of failure
	 */
	protected function retrieveModels($mapperName, TYPO3AJAX $ajaxObject) {
		$ajaxObject->setContentFormat('json');

		if (!class_exists($mapperName)) {
			throw new InvalidArgumentException(
				'A mapper with the name "' . $mapperName .
					'" could not be found.'
			);
		}

		$pageUid = intval(t3lib_div::_POST('id'));
		if (!$this->isPageUidValid($pageUid)) {
			$ajaxObject->setContent(array('success' => FALSE));
			return;
		}

		$mapper = tx_oelib_MapperRegistry::get($mapperName);
		return $mapper->findByPageUid(
			tx_oelib_db::createRecursivePageList($pageUid, 255)
		);
	}

	/**
	 * Checks whether the given page UID refers to a valid, existing system
	 * folder.
	 *
	 * @param integer $pageUid the page UID to check, may also be 0 or negative
	 *
	 * @return boolean TRUE if $pageUid is a valid system folder, FALSE otherwise
	 */
	protected function isPageUidValid($pageUid) {
		if ($pageUid <= 0) {
			return FALSE;
		}

		return tx_oelib_db::existsRecordWithUid(
			'pages',
			$pageUid,
			' AND doktype = 254' . tx_oelib_db::enableFields('pages', 1)
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/class.tx_seminars_BackEndExtJs_Ajax.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/class.tx_seminars_BackEndExtJs_Ajax.php']);
}
?>