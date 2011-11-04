<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2011 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'events list' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_BackEnd_EventsList extends tx_seminars_BackEnd_AbstractList {
	/**
	 * @var string the name of the table we're working on
	 */
	protected $tableName = 'tx_seminars_seminars';

	/**
	 * @var tx_seminars_seminar the seminar which we want to list
	 */
	private $seminar = null;

	/**
	 * @var string the path to the template file of this list
	 */
	protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/EventsList.html';

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->seminar) {
			$this->seminar->__destruct();
			unset($this->seminar);
		}

		parent::__destruct();
	}

	/**
	 * Generates and prints out an event list.
	 *
	 * @return string the HTML source code of the event list
	 */
	public function show() {
		$content = '';

		$this->createTableHeading();

		$builder = tx_oelib_ObjectFactory::make('tx_seminars_BagBuilder_Event');
		$builder->setBackEndMode();

		$pageData = $this->page->getPageData();
		$builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

		$seminarBag = $builder->build();
		$this->createListBody($seminarBag);

		$this->template->setMarker(
			'new_record_button', $this->getNewIcon($pageData['uid'])
		);

		$this->template->setMarker(
			'csv_event_export_button',
			(!$seminarBag->isEmpty() ? $this->getCsvIcon() : '')
		);
		$this->template->setMarker(
			'label_print_button', $GLOBALS['LANG']->getLL('print')
		);

		$content .= $this->template->getSubpart('SEMINARS_EVENT_LIST');

		// Checks the BE configuration and the CSV export configuration.
		$content .= $seminarBag->checkConfiguration();
		$seminarBag->__destruct();

		return $content;
	}

	/**
	 * Sets the labels for the heading for the events table.
	 *
	 * The labels are set directly in the template, so nothing is returned.
	 */
	private function createTableHeading() {
		$this->template->setMarker(
			'label_accreditation_number',
			$GLOBALS['LANG']->getLL('eventlist.accreditation_number')
		);
		$this->template->setMarker(
			'label_title', $GLOBALS['LANG']->getLL('eventlist.title')
		);
		$this->template->setMarker(
			'label_date', $GLOBALS['LANG']->getLL('eventlist.date')
		);
		$this->template->setMarker(
			'label_attendees', $GLOBALS['LANG']->getLL('eventlist.attendees')
		);
		$this->template->setMarker(
			'label_number_of_attendees_on_queue',
			$GLOBALS['LANG']->getLL('eventlist.attendeesOnRegistrationQueue')
		);
		$this->template->setMarker(
			'label_minimum_number_of_attendees',
			$GLOBALS['LANG']->getLL('eventlist.attendees_min')
		);
		$this->template->setMarker(
			'label_maximum_number_of_attendees',
			$GLOBALS['LANG']->getLL('eventlist.attendees_max')
		);
		$this->template->setMarker(
			'label_has_enough_attendees',
			$GLOBALS['LANG']->getLL('eventlist.enough_attendees')
		);
		$this->template->setMarker(
			'label_is_fully_booked', $GLOBALS['LANG']->getLL('eventlist.is_full')
		);
		$this->template->setMarker(
			'label_status', $GLOBALS['LANG']->getLL('eventlist_status')
		);
	}

	/**
	 * Creates all table rows for the list view.
	 *
	 * The table rows are set directly in the template, so nothing is returned.
	 *
	 * @param tx_seminars_Bag_Event $events the events to list
	 */
	private function createListBody(tx_seminars_Bag_Event $events) {
		$tableRows = '';

		foreach ($events as $event) {
			$this->template->setMarker('uid', $event->getUid());
			$this->template->setMarker('icon', $event->getRecordIcon());
			$this->template->setMarker(
				'accreditation_number',
				htmlspecialchars($event->getAccreditationNumber())
			);
			$this->template->setMarker(
				'title',
				htmlspecialchars(
					t3lib_div::fixed_lgd_cs(
						$event->getRealTitle(),
						$GLOBALS['BE_USER']->uc['titleLen']
					)
				)
			);
			$this->template->setMarker(
				'date', ($event->hasDate() ? $event->getDate() : '')
			);
			$this->template->setMarker(
				'edit_button',
				$this->getEditIcon($event->getUid(), $event->getPageUid())
			);
			$this->template->setMarker(
				'delete_button',
				$this->getDeleteIcon($event->getUid(), $event->getPageUid())
			);
			$this->template->setMarker(
				'hide_unhide_button',
				$this->getHideUnhideIcon(
					$event->getUid(), $event->getPageUid(), $event->isHidden()
				)
			);
			$this->template->setMarker(
				'csv_registration_export_button',
				(($event->needsRegistration() && !$event->isHidden())
					? $this->getRegistrationsCsvIcon($event) : '')
			);
			$this->template->setMarker(
				'number_of_attendees',
				($event->needsRegistration() ? $event->getAttendances() : '')
			);
			$this->template->setMarker(
				'show_registrations',
				((!$event->isHidden()
					&& $event->needsRegistration()
					&& $event->hasAttendances())
					? $this->createEventRegistrationsLink($event) : ''
				)
			);
			$this->template->setMarker(
				'number_of_attendees_on_queue',
				($event->hasRegistrationQueue()
					? $event->getAttendancesOnRegistrationQueue() : '')
			);
			$this->template->setMarker(
				'minimum_number_of_attendees',
				($event->needsRegistration() ? $event->getAttendancesMin() : '')
			);
			$this->template->setMarker(
				'maximum_number_of_attendees',
				($event->needsRegistration() ? $event->getAttendancesMax() : '')
			);
			$this->template->setMarker(
				'has_enough_attendees',
				($event->needsRegistration()
					? (!$event->hasEnoughAttendances()
						? $GLOBALS['LANG']->getLL('no') : $GLOBALS['LANG']->getLL('yes'))
					: '')
			);
			$this->template->setMarker(
				'is_fully_booked',
				($event->needsRegistration()
					? (!$event->isFull()
						? $GLOBALS['LANG']->getLL('no') : $GLOBALS['LANG']->getLL('yes'))
					: '')
			);
			$this->template->setMarker(
				'status', $this->getStatusIcon($event)
			);

			$this->setEmailButtonMarkers($event);
			$this->setCancelButtonMarkers($event);
			$this->setConfirmButtonMarkers($event);

			$tableRows .= $this->template->getSubpart('EVENT_ROW');
		}

		$this->template->setSubpart('EVENT_ROW', $tableRows);
	}

	/**
	 * Returns an HTML image tag for an icon that represents the status "canceled"
	 * or "confirmed". If the event's status is "planned", an empty string will be
	 * returned.
	 *
	 * @param tx_seminars_seminar $event the event to get the status icon for
	 *
	 * @return string HTML image tag, may be empty
	 */
	private function getStatusIcon(tx_seminars_seminar $event) {
		if (!$event->isCanceled() && !$event->isConfirmed()) {
			return '';
		}

		if ($event->isConfirmed()) {
			$icon = 'Confirmed.png';
			$labelKey = 'eventlist_status_confirmed';
		} elseif ($event->isCanceled()) {
			$icon = 'Canceled.png';
			$labelKey = 'eventlist_status_canceled';
		}
		$label = $GLOBALS['LANG']->getLL($labelKey);

		return '<img src="../Resources/Public/Icons/' . $icon . '" title="' .
			$label . '" alt="' . $label . '" />';
	}

	/**
	 * Generates a linked CSV export icon for registrations from $this->seminar
	 * if that event has at least one registration and access to all involved
	 * registration records is granted.
	 *
	 * @param tx_seminars_seminar $event
	 *        the event to get the registrations CSV icon for
	 *
	 * @return string the HTML for the linked image (followed by a non-breaking
	 *                space) or an empty string
	 */
	public function getRegistrationsCsvIcon(tx_seminars_seminar $event) {
		static $accessChecker = null;
		if (!$accessChecker) {
			$accessChecker = tx_oelib_ObjectFactory::make('tx_seminars_pi2');
			$accessChecker->init();
		}

		$result = '';

		$eventUid = $event->getUid();

		if ($event->hasAttendances()
			&& $accessChecker->canAccessListOfRegistrations($eventUid)) {
			$pageData = $this->page->getPageData();
			$langCsv = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.csv', 1);
			$result = '<a href="class.tx_seminars_BackEnd_CSV.php?id=' .
				$pageData['uid'] .
				'&amp;tx_seminars_pi2[table]=tx_seminars_attendances' .
				'&amp;tx_seminars_pi2[eventUid]=' . $eventUid . '">' .
				'<img' .
				t3lib_iconWorks::skinImg(
					$GLOBALS['BACK_PATH'],
					'gfx/csv.gif',
					'width="27" height="14"'
				) .
				' title="' . $langCsv . '" alt="' . $langCsv . '" class="icon" />' .
				'</a>&nbsp;';
		}

		return $result;
	}

	/**
	 * Sets the markers of a button for sending an e-mail to the attendees of an
	 * event.
	 *
	 * The button will only be visible if the event has at least one registration.
	 *
	 * @param tx_seminars_seminar $event the event to get the e-mail button for
	 */
	private function setEmailButtonMarkers(tx_seminars_seminar $event) {
		if (!$event->hasAttendances()) {
			$this->template->hideSubpartsArray(array('EMAIL_BUTTON'));
			return;
		}

		$this->template->unhideSubpartsArray(array('EMAIL_BUTTON'));
		$pageData = $this->page->getPageData();

		$this->template->setMarker('uid', $event->getUid());
		$this->template->setMarker(
			'email_button_url',
			'index.php?id=' . $pageData['uid'] . '&amp;subModule=1'
		);
		$this->template->setMarker(
			'label_email_button',
			$GLOBALS['LANG']->getLL('eventlist_button_email')
			);
	}

	/**
	 * Sets the markers of a button for canceling an event. The button will only
	 * be visible if
	 * - the current record is either a date or single event record
	 * - the event is not canceled yet
	 * - the event has not started yet
	 * In all other cases the corresponding subpart is hidden.
	 *
	 * @param tx_seminars_seminar $event the event to get the cancel button for
	 */
	private function setCancelButtonMarkers(tx_seminars_seminar $event) {
		$this->template->unhideSubpartsArray(array('CANCEL_BUTTON'));
		$pageData = $this->page->getPageData();

		if (($event->getRecordType() != tx_seminars_Model_Event::TYPE_TOPIC)
			&& !$event->isHidden() && !$event->isCanceled()
			&& !$event->hasStarted()
			&& $GLOBALS['BE_USER']->check('tables_modify', $this->tableName)
			&& $this->doesUserHaveAccess($event->getPageUid())
		) {
			$this->template->setMarker('uid', $event->getUid());
			$this->template->setMarker(
				'cancel_button_url',
				'index.php?id=' . $pageData['uid'] . '&amp;subModule=1'
			);
			$this->template->setMarker(
				'label_cancel_button',
				$GLOBALS['LANG']->getLL('eventlist_button_cancel')
			);
		} else {
			$this->template->hideSubpartsArray(array('CANCEL_BUTTON'));
		}
	}

	/**
	 * Sets the markers of a button for confirming an event. The button will
	 * only be visible if
	 * - the current record is either a date or single event record
	 * - the event is not confirmed yet
	 * - the event has not started yet
	 * In all other cases the corresponding subpart is hidden.
	 *
	 * @param tx_seminars_seminar $event the event to get the confirm button for
	 */
	private function setConfirmButtonMarkers(tx_seminars_seminar $event) {
		$this->template->unhideSubpartsArray(array('CONFIRM_BUTTON'));
		$pageData = $this->page->getPageData();

		if (($event->getRecordType() != tx_seminars_Model_Event::TYPE_TOPIC)
			&& !$event->isHidden() && !$event->isConfirmed()
			&& !$event->hasStarted()
			&& $GLOBALS['BE_USER']->check('tables_modify', $this->tableName)
			&& $this->doesUserHaveAccess($event->getPageUid())
		) {
			$this->template->setMarker('uid', $event->getUid());
			$this->template->setMarker(
				'confirm_button_url',
				'index.php?id=' . $pageData['uid'] . '&amp;subModule=1'
			);
			$this->template->setMarker(
				'label_confirm_button',
				$GLOBALS['LANG']->getLL('eventlist_button_confirm')
			);
		} else {
			$this->template->hideSubpartsArray(array('CONFIRM_BUTTON'));
		}
	}

	/**
	 * Returns the storage folder for new event records.
	 *
	 * This will be determined by the event folder storage setting of the
	 * currently logged-in BE-user.
	 *
	 * @return integer the PID for new event records, will be >= 0
	 */
	protected function getNewRecordPid() {
		return $this->getLoggedInUser()->getEventFolderFromGroup();
	}

	/**
	 * Creates a link to the registrations page, showing the attendees for the
	 * given event UID.
	 *
	 * @param tx_seminars_seminar $event
	 *        the event to show the registrations for, must be >= 0
	 *
	 * @return string the URL to the registrations tab with the registration for
	 *                the current event, will not be empty
	 */
	private function createEventRegistrationsLink(tx_seminars_seminar $event) {
		$pageData = $this->page->getPageData();

		return '<a href="index.php?id=' . $pageData['uid'] .
			'&amp;subModule=2&amp;eventUid=' . $event->getUid() . '">' .
			$GLOBALS['LANG']->getLL('label_show_event_registrations') .
			'</a>';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/EventsList.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/EventsList.php']);
}
?>