<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'registrations list' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEnd_RegistrationsList extends tx_seminars_BackEnd_List {
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
	 * Generates and prints out a registrations list.
	 *
	 * @return string the HTML source code to display
	 */
	public function show() {
		$content = '';

		$pageData = $this->page->getPageData();

		$this->template->setMarker(
			'new_record_button', $this->getNewIcon($pageData['uid'])
		);

		$this->template->setMarker(
			'label_regular_registrations',
			$GLOBALS['LANG']->getLL('registrationlist.label_regularRegistrations')
		);
		$this->template->setMarker(
			'label_registrations_on_queue',
			$GLOBALS['LANG']->getLL('registrationlist.label_queueRegistrations')
		);
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

		$this->setRegistrationTableMarkers(false);
		$this->setRegistrationTableMarkers(true);

		$content .= $this->template->getSubpart('SEMINARS_REGISTRATION_LIST');

		$content .= $this->configCheckWarnings;

		return $content;
	}

	/**
	 * Gets the registration table for regular attendances and attendances on
	 * the registration queue.
	 *
	 * @param boolean True if the registration table for the registration
	 *                queue should be generated and false if the table for
	 *                the regular attendances should be generated.
	 */
	private function setRegistrationTableMarkers($showRegistrationQueue) {
		$builder = tx_oelib_ObjectFactory::make('tx_seminars_registrationBagBuilder');
		$pageData = $this->page->getPageData();
		$builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

		if ($showRegistrationQueue) {
			$builder->limitToOnQueue();
		} else {
			$builder->limitToRegular();
		}

		$registrationBag = $builder->build();

		if ($showRegistrationQueue) {
			$this->template->setMarker(
				'number_of_registrations_on_queue', $registrationBag->count()
			);
		} else {
			$this->template->setMarker(
				'number_of_regular_registrations', $registrationBag->count()
			);
		}

		$tableRows = '';

		foreach ($registrationBag as $registration) {
			try {
				$userName = htmlspecialchars($registration->getUserName());
			} catch (tx_oelib_Exception_NotFound $e) {
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
					$registration->getUid(), $registration->getPageUid
				)
			);
			$this->template->setMarker(
				'delete_button',
				$this->getDeleteIcon(
					$registration->getUid(), $registration->getPageUid
				)
			);

			$tableRows .= ($showRegistrationQueue
				? $this->template->getSubpart('REGISTRATION_ON_QUEUE_ROW')
				: $this->template->getSubpart('REGULAR_REGISTRATION_ROW'));
		}

		if ($this->configCheckWarnings == '') {
			$this->configCheckWarnings =
				$registrationBag->checkConfiguration();
		}
		$registrationBag->__destruct();

		if ($showRegistrationQueue) {
			$this->template->setSubpart(
				'REGISTRATION_ON_QUEUE_ROW', $tableRows
			);
		} else {
			$this->template->setSubpart(
				'REGULAR_REGISTRATION_ROW', $tableRows
			);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_RegistrationsList.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_RegistrationsList.php']);
}
?>