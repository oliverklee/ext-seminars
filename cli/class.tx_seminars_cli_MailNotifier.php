<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Saskia Metzler <saskia@merlin.owl.de>
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

require_once(t3lib_extMgm::extPath('seminars') . 'pi2/class.tx_seminars_pi2.php');

/**
 * Class 'tx_seminars_cli_MailNotifier' for the 'seminars' extension.
 *
 * This class sends reminders to the organizers.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_seminars_cli_MailNotifier {
	/**
	 * Starts the CLI module.
	 */
	public function start() {
		$this->setConfigurationPage();
		$this->sendEventTakesPlaceReminders();
		$this->sendCancelationDeadlineReminders();
	}

	/**
	 * Checks whether the UID provided as the second argument when starting the
	 * CLI script actually exists in the "pages" table. If the page UID is
	 * valid, defines this UID as the one where to take the configuration from,
	 * otherwise throws an exception.
	 *
	 * @throws Exception if no page UID or an invalid UID was provided
	 */
	public function setConfigurationPage() {
		if (!isset($_SERVER['argv'][1])) {
			throw new Exception(
				'Please provide the UID for the page with the configuration ' .
				'for the CLI module.'
			);
		}

		$uid = intval($_SERVER['argv'][1]);
		if (($uid == 0) ||
			(tx_oelib_db::selectSingle(
				'COUNT(*) AS number', 'pages', 'uid = ' . $uid
			) != array('number' => 1))
		) {
			throw new Exception(
				'The provided UID for the page with the configuration was ' .
				$_SERVER['argv'][1] . ', which was not found to be a UID of ' .
				'an existing page. Please provide the UID of an existing page.'
			);
		}

		tx_oelib_PageFinder::getInstance()->setPageUid($uid);
	}

	/**
	 * Sends event-takes-place reminders to the corresponding organizers and
	 * commits the flag for this reminder being sent to the database.
	 */
	public function sendEventTakesPlaceReminders() {
		foreach ($this->getEventsToSendEventTakesPlaceReminderFor() as $event) {
			$this->sendRemindersToOrganizers(
				$event,
				'Event-takes-place reminder',
				'The event is about to take place.'
			);
			$event->setEventTakesPlaceReminderSentFlag();
			$event->commitToDb();
		}
	}

	/**
	 * Sends cancelation deadline reminders to the corresponding organizers and
	 * commits the flag for this reminder being sent to the database.
	 */
	public function sendCancelationDeadlineReminders() {
		foreach ($this->getEventsToSendCancelationDeadlineReminderFor() as $event) {
			$this->sendRemindersToOrganizers(
				$event,
				'Cancelation deadline reminder',
				'The cancelation deadline has just passed.'
			);
			$event->setCancelationDeadlineReminderSentFlag();
			$event->commitToDb();
		}
	}

	/**
	 * Sends an e-mail to the organizers of the provided event.
	 *
	 * @param tx_seminars_seminar event for which to send the reminder to its
	 *                            organizers
	 * @param string subject for the e-mail to send, must not be empty
	 * @param string message content for the e-mail to send, must not be empty
	 */
	private function sendRemindersToOrganizers(
		tx_seminars_seminar $event, $subject, $message
	) {
		$eMail = t3lib_div::makeInstance('tx_oelib_Mail');
		$organizerBag = $event->getOrganizerBag();

		// The first organizer is taken as sender.
		$eMail->setSender($organizerBag->current());
		$eMail->setSubject($subject);
		$eMail->setMessage($message);
		$eMail->addAttachment($this->getCsv($event->getUid()));
		foreach ($organizerBag as $organizer) {
			$eMail->addRecipient($organizer);
		}

		tx_oelib_mailerFactory::getInstance()->getMailer()->send($eMail);

		$organizerBag->__destruct();
		$eMail->__destruct();
	}

	/**
	 * Returns events in confirmed state which are about to take place and for
	 * which no reminder has been sent yet.
	 *
	 * @return array events for which to send the event-takes-place reminder to
	 *               their organizers, will be empty if there are none
	 */
	private function getEventsToSendEventTakesPlaceReminderFor() {
		$days = $this->getDaysBeforeBeginDate();
		if ($days == 0) {
			return array();
		}

		$result = array();

		$builder
			= $this->getSeminarBagBuilder(tx_seminars_seminar::STATUS_CONFIRMED);
		$builder->limitToEventTakesPlaceReminderNotSent();
		$builder->limitToDaysBeforeBeginDate($days);
		$bag = $builder->build();

		foreach ($bag as $event) {
			$result[] = $event;
		}

		$bag->__destruct();

		return $result;
	}

	/**
	 * Returns events in planned state for which the cancelation deadline has
	 * just passed and for which no reminder has been sent yet.
	 *
	 * @return array events for which to send the cancelation reminder to their
	 *               organizers, will be empty if there are none
	 */
	private function getEventsToSendCancelationDeadlineReminderFor() {
		if (!tx_oelib_ConfigurationRegistry::getInstance()
			->get('plugin.tx_seminars')->getAsBoolean(
				'sendCancelationDeadlineReminder'
			)
		) {
			return array();
		}

		$result = array();

		$builder
			= $this->getSeminarBagBuilder(tx_seminars_seminar::STATUS_PLANNED);
		$builder->limitToCancelationDeadlineReminderNotSent();
		$bag = $builder->build();

		foreach ($bag as $event) {
			if ($event->getCancelationDeadline() < $GLOBALS['SIM_EXEC_TIME']) {
				$result[] = $event;
			}
		}

		$bag->__destruct();

		return $result;
	}

	/**
	 * Returns the TS setup configuration value of
	 * 'sendEventTakesPlaceReminderDaysBeforeBeginDate'.
	 *
	 * @return integer how many days before an event the event-takes-place
	 *                 reminder should be send, will be > 0 if this option is
	 *                 enabled, zero disables sending the reminder
	 */
	private function getDaysBeforeBeginDate() {
		return tx_oelib_ConfigurationRegistry::getInstance()
			->get('plugin.tx_seminars')
			->getAsInteger('sendEventTakesPlaceReminderDaysBeforeBeginDate');
	}

	/**
	 * Returns a seminar bag builder already limited to upcoming events with a
	 * begin date and status $status.
	 *
	 * @param integer status to limit the builder to, either
	 *                tx_seminars_seminar::STATUS_PLANNED or ::CONFIRMED
	 *
	 * @return tx_seminars_seminarbagbuilder builder for the seminar bag
	 */
	private function getSeminarBagBuilder($status) {
		$builder = t3lib_div::makeInstance('tx_seminars_seminarbagbuilder');
		$builder->setTimeFrame('upcomingWithBeginDate');
		$builder->limitToStatus($status);

		return $builder;
	}

	/**
	 * Returns the CSV output for the list of registrations for the event with
	 * the provided UID.
	 *
	 * @param integer UID of the event to create the output for, must be > 0
	 *
	 * @return tx_oelib_Attachment CSV list of registrations for the given
	 *                             seminar
	 */
	private function getCsv($uid) {
		$configuration = tx_oelib_ConfigurationRegistry
			::getInstance()->get('plugin.tx_seminars');

		$csvCreator = t3lib_div::makeInstance('tx_seminars_pi2');
		$csvCreator->init();
		foreach (array(
			'fieldsFromFeUserForCsv' => getAsString,
			'fieldsFromAttendanceForCsv' => getAsString,
			'showAttendancesOnRegistrationQueueInCSV' => getAsBoolean,
		) as $key => $getterName) {
			$csvCreator->getConfigGetter()->setConfigurationValue(
				$key, $configuration->$getterName($key)
			);
		}
		$csvString = $csvCreator->createListOfRegistrations($uid);
		$csvCreator->__destruct();

		$attachment = t3lib_div::makeInstance('tx_oelib_Attachment');
		$attachment->setContent($csvString);
		$attachment->setContentType('text/csv');
		$attachment->setFileName($configuration->getAsString(
			'filenameForRegistrationsCsv'
		));

		return $attachment;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/cli/class.tx_seminars_cli_MailNotifier.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realty/cli/class.tx_seminars_cli_MailNotifier.php']);
}
?>