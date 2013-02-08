<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Niels Pardon (mail@niels-pardon.de)
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
 * This class represents a countdown to the next upcoming event.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Mario Rimann <typo3-coding@rimann.org>
 */
class tx_seminars_FrontEnd_Countdown extends tx_seminars_FrontEnd_AbstractView {
	/**
	 * @var tx_seminars_Mapper_Event
	 */
	protected $mapper = NULL;

	/**
	 * @var tx_seminars_ViewHelper_Countdown
	 */
	protected $viewHelper = NULL;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		parent::__destruct();
		unset($this->mapper, $this->viewHelper);
	}

	/**
	 * Injects an Event Mapper for this View.
	 *
	 * @param tx_seminars_Mapper_Event $mapper
	 *
	 * @return void
	 */
	public function injectEventMapper(tx_seminars_Mapper_Event $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Injects an Countdown View Helper.
	 *
	 * @param tx_seminars_ViewHelper_Countdown $viewHelper
	 *
	 * @return void
	 */
	public function injectCountDownViewHelper(tx_seminars_ViewHelper_Countdown $viewHelper) {
		$this->viewHelper = $viewHelper;
	}

	/**
	 * Creates a countdown to the next upcoming event.
	 *
	 * @return string HTML code of the countdown or a message if no upcoming event has been found
	 */
	public function render() {
		if ($this->mapper === NULL) {
			throw new BadMethodCallException('The method injectEventMapper() needs to be called first.', 1333617194);
		}
		if ($this->viewHelper === NULL) {
			$this->injectCountDownViewHelper(tx_oelib_ObjectFactory::make('tx_seminars_ViewHelper_Countdown'));
		}

		$this->setErrorMessage($this->checkConfiguration(TRUE));

		try {
			$event = $this->mapper->findNextUpcoming();

			$message = $this->viewHelper->render($event->getBeginDateAsUnixTimestamp());
		} catch (tx_oelib_Exception_NotFound $exception) {
			$message = $this->translate('message_countdown_noEventFound');
		}

		$this->setMarker('count_down_message', $message);

		return $this->getSubpart('COUNTDOWN');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/Countdown.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/Countdown.php']);
}
?>