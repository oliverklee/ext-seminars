<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Bernd Schönbach <bernd@oliverklee.de>
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
 * This class displays an event headline consisting of the event title and date.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_FrontEnd_EventHeadline extends tx_seminars_FrontEnd_AbstractView {
	/**
	 * @var tx_seminars_Mapper_Event
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
	 * @param tx_seminars_Mapper_Event $mapper
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

		$eventId = intval($this->piVars['uid']);
		if ($eventId <= 0) {
			return '';
		}

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

		$dateRangeViewHelper = tx_oelib_ObjectFactory::make('tx_seminars_ViewHelper_DateRange');

		return $result . ', ' . $dateRangeViewHelper->render($event);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/EventHeadline.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/EventHeadline.php']);
}