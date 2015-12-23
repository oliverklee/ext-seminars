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
 * This class displays an event headline consisting of the event title and date.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_FrontEnd_EventHeadline extends Tx_Seminars_FrontEnd_AbstractView {
	/**
	 * @var Tx_Seminars_Mapper_Event
	 */
	protected $mapper = NULL;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->mapper);

		parent::__destruct();
	}

	/**
	 * Injects an Event Mapper for this View.
	 *
	 * @param Tx_Seminars_Mapper_Event $mapper
	 *
	 * @return void
	 */
	public function injectEventMapper($mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Creates the event headline, consisting of the event title and date.
	 *
	 * @return string HTML code of the event headline, will be empty if an invalid or no event ID was set in piVar 'uid'
	 */
	public function render() {
		if ($this->mapper === NULL) {
			throw new BadMethodCallException("The method injectEventMapper() needs to be called first.", 1333614794);
		}

		$eventId = (int)$this->piVars['uid'];
		if ($eventId <= 0) {
			return '';
		}

		/** @var tx_seminars_Model_Event $event */
		$event = $this->mapper->find($eventId);

		if (!$this->mapper->existsModel($eventId)) {
			return '';
		}

		$this->setMarker('title_and_date', $this->getTitleAndDate($event));
		$result = $this->getSubpart('VIEW_HEADLINE');

		$this->setErrorMessage($this->checkConfiguration(TRUE));

		return $result;
	}

	/**
	 * Gets the unique event title, consisting of the event title and the date (comma-separated).
	 *
	 * If the event has no date, just the title is returned.
	 *
	 * @param tx_seminars_Model_Event $event the event to get the unique event title for
	 *
	 * @return string the unique event title (or '' if there is an error)
	 */
	protected function getTitleAndDate(tx_seminars_Model_Event $event) {
		$result = htmlspecialchars($event->getTitle());
		if (!$event->hasBeginDate()) {
			return $result;
		}

		/** @var tx_seminars_ViewHelper_DateRange $dateRangeViewHelper */
		$dateRangeViewHelper = GeneralUtility::makeInstance('tx_seminars_ViewHelper_DateRange');

		return $result . ', ' . $dateRangeViewHelper->render($event);
	}
}