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
		$mapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
		$events = $mapper->findByPageUid(intval($parameters['id']));

		$rows = array();
		foreach ($events as $event) {
			$rows[] = array(
				'iconCls' => $this->getIconClass($event),
				'uid' => $event->getUid(),
				'hidden' => $event->isHidden(),
				'status' => $event->getStatus(),
				'title' => $event->getTitle(),
				'begin_date' => $event->getBeginDateAsUnixTimeStamp(),
				'end_date' => $event->getEndDateAsUnixTimeStamp(),
			);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
		$ajaxObject->setContentFormat('json');
	}

	/**
	 * Returns the CSS icon class name for the given event based on the type of
	 * the event record, e.g.:
	 * - "typo3-backend-seminars-event-topic-icon" for event topics
	 * - "typo3-backend-seminars-event-single-icon" for single events
	 * - "typo3-backend-seminars-event-date-icon" for event dates
	 *
	 * @param tx_seminars_Model_Event $event
	 *        the event to get the CSS icon class name for
	 *
	 * @return string the CSS icon class name for the given event
	 */
	private function getIconClass(tx_seminars_Model_Event $event) {
		$result = 'typo3-backend-seminars-event-topic-icon';

		if ($event->isSingleEvent()) {
			$result = 'typo3-backend-seminars-event-single-icon';
		} elseif ($event->isEventDate()) {
			$result = 'typo3-backend-seminars-event-date-icon';
		}

		return $result;
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
		$mapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration');
		$registrations = $mapper->findByPageUid(intval($parameters['id']));

		$rows = array();
		foreach ($registrations as $registration) {
			$rows[] = array(
				'uid' => $registration->getUid(),
				'title' => $registration->getTitle(),
			);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
		$ajaxObject->setContentFormat('json');
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
		$mapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker');
		$speakers = $mapper->findByPageUid(intval($parameters['id']));

		$rows = array();
		foreach ($speakers as $speaker) {
			$rows[] = array(
				'uid' => $speaker->getUid(),
				'title' => $speaker->getName(),
			);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
		$ajaxObject->setContentFormat('json');
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
		$mapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer');
		$organizers = $mapper->findByPageUid(intval($parameters['id']));

		$rows = array();
		foreach ($organizers as $organizer) {
			$rows[] = array(
				'uid' => $organizer->getUid(),
				'title' => $organizer->getName(),
			);
		}

		$ajaxObject->setContent(array(
			'success' => TRUE,
			'rows' => $rows,
		));
		$ajaxObject->setContentFormat('json');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/class.tx_seminars_BackEndExtJs_Ajax.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/class.tx_seminars_BackEndExtJs_Ajax.php']);
}
?>