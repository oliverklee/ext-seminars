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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a registration list in the back end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_BackEnd_RegistrationsList extends Tx_Seminars_BackEnd_AbstractList {
	/**
	 * @var string the name of the table we're working on
	 */
	protected $tableName = 'tx_seminars_attendances';

	/**
	 * @var string warnings from the registration bag configcheck
	 */
	private $configCheckWarnings = '';

	/**
	 * @var string the path to the template file of this list
	 */
	protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/RegistrationsList.html';

	/**
	 * @var int parameter for setRegistrationTableMarkers to show the list
	 *              of registrations on the queue
	 */
	const REGISTRATIONS_ON_QUEUE = 1;

	/**
	 * @var int parameter for setRegistrationTableMarkers to show the list
	 *              of regular registrations
	 */
	const REGULAR_REGISTRATIONS = 2;

	/**
	 * @var int the UID of the event to show the registrations for
	 */
	private $eventUid = 0;

	/**
	 * Generates and prints out a registrations list.
	 *
	 * @return string the HTML source code to display
	 */
	public function show() {
		$content = '';

		$pageData = $this->page->getPageData();

		$this->template->setMarker(
			'label_attendee_full_name',
			$GLOBALS['LANG']->getLL('registrationlist.feuser.name')
		);
		$this->template->setMarker(
			'label_event_accreditation_number',
			$GLOBALS['LANG']->getLL('registrationlist.seminar.accreditation_number')
		);
		$this->template->setMarker(
			'label_event_title',
			$GLOBALS['LANG']->getLL('registrationlist.seminar.title')
		);
		$this->template->setMarker(
			'label_event_date',
			$GLOBALS['LANG']->getLL('registrationlist.seminar.date')
		);

		$eventUid = (int)GeneralUtility::_GP('eventUid');
		/** @var tx_seminars_Mapper_Event $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
		if (($eventUid > 0) && $mapper->existsModel($eventUid)) {
			$this->eventUid = $eventUid;
			/** @var tx_seminars_Model_Event $event */
			$event = $mapper->find($eventUid);
			$registrationsHeading = sprintf(
				$GLOBALS['LANG']->getLL('registrationlist.label_registrationsHeading'),
				htmlspecialchars($event->getTitle()),
				$event->getUid()
			);
			$newButton = '';
		} else {
			$registrationsHeading = '';
			$newButton = $this->getNewIcon($pageData['uid']);
		}

		$areAnyRegularRegistrationsVisible = $this->setRegistrationTableMarkers(
			self::REGULAR_REGISTRATIONS
		);
		$registrationTables = $this->template->getSubpart('REGISTRATION_TABLE');
		$this->setRegistrationTableMarkers(self::REGISTRATIONS_ON_QUEUE);
		$registrationTables .= $this->template->getSubpart('REGISTRATION_TABLE');

		$this->template->setOrDeleteMarkerIfNotEmpty(
			'registrations_heading', $registrationsHeading, '','wrapper'
		);
		$this->template->setMarker('new_record_button', $newButton);
		$this->template->setMarker(
			'csv_export_button',
			($areAnyRegularRegistrationsVisible ? $this->getCsvIcon() : '')
		);
		$this->template->setMarker('complete_table', $registrationTables);
		$this->template->setMarker(
			'label_print_button', $GLOBALS['LANG']->getLL('print')
		);

		$content .= $this->template->getSubpart('SEMINARS_REGISTRATION_LIST');
		$content .= $this->configCheckWarnings;

		return $content;
	}

	/**
	 * Gets the registration table for regular attendances and attendances on
	 * the registration queue.
	 *
	 * If an event UID > 0 in $this->eventUid is set, the registrations of this
	 * event will be listed, otherwise the registrations on the current page and
	 * subpages will be listed.
	 *
	 * @param int $registrationsToShow
	 *        the switch to decide which registrations should be shown, must
	 *        be either
	 *        Tx_Seminars_BackEnd_RegistrationsList::REGISTRATIONS_ON_QUEUE or
	 *        Tx_Seminars_BackEnd_RegistrationsList::REGULAR_REGISTRATIONS
	 *
	 * @return bool TRUE if the generated list is not empty, FALSE otherwise
	 */
	private function setRegistrationTableMarkers($registrationsToShow) {
		/** @var Tx_Seminars_BagBuilder_Registration $builder */
		$builder = GeneralUtility::makeInstance(Tx_Seminars_BagBuilder_Registration::class);
		$pageData = $this->page->getPageData();

		switch ($registrationsToShow) {
			case self::REGISTRATIONS_ON_QUEUE:
				$builder->limitToOnQueue();
				$tableLabel = 'registrationlist.label_queueRegistrations';
				break;
			case self::REGULAR_REGISTRATIONS:
				$builder->limitToRegular();
				$tableLabel = 'registrationlist.label_regularRegistrations';
				break;
		}
		if ($this->eventUid > 0) {
			$builder->limitToEvent($this->eventUid);
		} else {
			$builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);
		}

		$registrationBag = $builder->build();
		$result = !$registrationBag->isEmpty();

		$tableRows = '';

		/** @var tx_seminars_registration $registration */
		foreach ($registrationBag as $registration) {
			try {
				$userName = htmlspecialchars($registration->getUserName());
			} catch (Tx_Oelib_Exception_NotFound $exception) {
				$userName = $GLOBALS['LANG']->getLL('registrationlist.deleted');
			}
			$event = $registration->getSeminarObject();
			if ($event->isOk()) {
				$eventTitle = htmlspecialchars($event->getTitle());
				$eventDate = $event->getDate();
				$accreditationNumber = htmlspecialchars(
					$event->getAccreditationNumber()
				);
			} else {
				$eventTitle = $GLOBALS['LANG']->getLL('registrationlist.deleted');
				$eventDate = '';
				$accreditationNumber = '';
			}

			$this->template->setMarker('icon', $registration->getRecordIcon());
			$this->template->setMarker('attendee_full_name', $userName);
			$this->template->setMarker('event_accreditation_number', $accreditationNumber);
			$this->template->setMarker('event_title', $eventTitle);
			$this->template->setMarker('event_date', $eventDate);
			$this->template->setMarker(
				'edit_button',
				$this->getEditIcon(
					$registration->getUid(), $registration->getPageUid()
				)
			);
			$this->template->setMarker(
				'delete_button',
				$this->getDeleteIcon(
					$registration->getUid(), $registration->getPageUid()
				)
			);

			$tableRows .= $this->template->getSubpart('REGISTRATION_TABLE_ROW');
		}

		if ($this->configCheckWarnings == '') {
			$this->configCheckWarnings =
				$registrationBag->checkConfiguration();
		}

		$this->template->setMarker(
			'label_registrations', $GLOBALS['LANG']->getLL($tableLabel)
		);
		$this->template->setMarker(
			'number_of_registrations', $registrationBag->count()
		);
		$this->template->setMarker(
			'table_header',
			$this->template->getSubpart('REGISTRATION_TABLE_HEADING')
		);
		$this->template->setMarker('table_rows', $tableRows);

		return $result;
	}

	/**
	 * Returns the storage folder for new registration records.
	 *
	 * This will be determined by the registration folder storage setting of the
	 * currently logged-in BE-user.
	 *
	 * @return int the PID for new registration records, will be >= 0
	 */
	protected function getNewRecordPid() {
		return $this->getLoggedInUser()->getRegistrationFolderFromGroup();
	}

	/**
	 * Returns the parameters to add to the CSV icon link.
	 *
	 * @return string the additional link parameters for the CSV icon link, will
	 *                always start with an &amp and be htmlspecialchared, may
	 *                be empty
	 */
	protected function getAdditionalCsvParameters() {
		if ($this->eventUid > 0) {
			$result = '&amp;tx_seminars_pi2[eventUid]=' . $this->eventUid;
		} else {
			$result = parent::getAdditionalCsvParameters();
		}

		return $result;
	}
}