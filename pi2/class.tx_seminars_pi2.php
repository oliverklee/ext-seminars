<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2007-2014 Oliver Klee (typo3-coding@oliverklee.de)
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

if (is_object($GLOBALS['LANG'])) {
	$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
}

require_once(t3lib_extMgm::extPath('seminars') . 'tx_seminars_modifiedSystemTables.php');

/**
 * Plugin "CSV export".
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_pi2 extends Tx_Oelib_TemplateHelper {
	/**
	 * @var int
	 */
	const CSV_TYPE_NUMBER = 736;

	/**
	 * @var int HTTP status code for "page not found"
	 */
	const NOT_FOUND = 404;

	/**
	 * @var int HTTP status code for "access denied"
	 */
	const ACCESS_DENIED = 403;

	/**
	 * @var string same as class name
	 */
	public $prefixId = 'tx_seminars_pi2';

	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi2/class.tx_seminars_pi2.php';

	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * @var string the TYPO3 mode set for testing purposes
	 */
	private $typo3Mode = '';

	/**
	 * @var int the HTTP status code of error
	 */
	private $errorType = 0;

	/**
	 * The constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->configuration = Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars');
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->configuration);

		parent::__destruct();
	}

	/**
	 * Creates a CSV export.
	 *
	 * @return string HTML for the plugin, might be empty
	 */
	public function main() {
		try {
			$this->init(array());

			switch ($this->piVars['table']) {
				case 'tx_seminars_seminars':
					$result = $this->createAndOutputListOfEvents((int)$this->piVars['pid']);
					break;
				case 'tx_seminars_attendances':
					$result = $this->createAndOutputListOfRegistrations((int)$this->piVars['eventUid']);
					break;
				default:
					$result = $this->addErrorHeaderAndReturnMessage(self::NOT_FOUND);
			}

			if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
				$dataCharset = 'utf-8';
			} else {
				$dataCharset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']
					? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'iso-8859-1';
			}
			$resultCharset = strtolower($this->configuration->getAsString('charsetForCsv'));
			if ($dataCharset !== $resultCharset) {
				$result = $this->getCharsetConversion()->conv($result, $dataCharset, $resultCharset);
			}
		} catch (Exception $exception) {
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 500 Internal Server Error');
			$result = $exception->getMessage() . LF . LF . $exception->getTraceAsString() . LF . LF;
		}

		return $result;
	}

	/**
	 * Retrieves an active charset conversion instance.
	 *
	 * @return t3lib_cs a charset conversion instance
	 *
	 * @throws RuntimeException
	 */
	protected function getCharsetConversion() {
		if (isset($GLOBALS['TSFE'])) {
			$instance = $GLOBALS['TSFE']->csConvObj;
		} elseif (isset($GLOBALS['LANG'])) {
			$instance = $GLOBALS['LANG']->csConvObj;
		} else {
			throw new RuntimeException('There was neither a front end nor a back end detected.', 1333292438);
		}

		return $instance;
	}

	/**
	 * Creates a CSV list of registrations for the event given in $eventUid, including a heading line.
	 *
	 * If the seminar does not exist, an error message is returned, and an error 404 is set.
	 *
	 * If access is denied, an error message is returned, and an error 403 is set.
	 *
	 * @param int $eventUid UID of the event for which to create the CSV list, must be >= 0
	 *
	 * @return string CSV list of registrations for the given seminar or an error message in case of an error
	 */
	public function createAndOutputListOfRegistrations($eventUid = 0) {
		/** @var $listView Tx_Seminars_Csv_EmailRegistrationListView */
		$listView = t3lib_div::makeInstance('Tx_Seminars_Csv_DownloadRegistrationListView');

		$pageUid = (integer) $this->piVars['pid'];
		if ($eventUid > 0) {
			if (!$this->hasAccessToEventAndItsRegistrations($eventUid)) {
				return $this->addErrorHeaderAndReturnMessage($this->errorType);
			}
			$listView->setEventUid($eventUid);
		} else {
			if (!$this->canAccessRegistrationsOnPage($pageUid)) {
				return $this->addErrorHeaderAndReturnMessage(self::ACCESS_DENIED);
			}
			$listView->setPageUid($pageUid);
		}

		$this->setContentTypeForRegistrationLists();

		return $listView->render();
	}

	/**
	 * Creates a CSV list of registrations for the event with the UID given in
	 * $eventUid, including a heading line.
	 *
	 * This function does not do any access checks.
	 *
	 * @param int $eventUid UID of the event for which the registration list should be created, must be > 0
	 *
	 * @return string CSV list of registrations for the given seminar or an
	 *                empty string if there is not event with the provided UID
	 */
	public function createListOfRegistrations($eventUid) {
		if (!tx_seminars_OldModel_Abstract::recordExists($eventUid, 'tx_seminars_seminars')) {
			return '';
		}

		/** @var $listView Tx_Seminars_Csv_EmailRegistrationListView */
		$listView = t3lib_div::makeInstance('Tx_Seminars_Csv_DownloadRegistrationListView');
		$listView->setEventUid($eventUid);

		return $listView->render();
	}

	/**
	 * Creates a CSV list of events for the page given in $pid.
	 *
	 * If the page does not exist, an error message is returned, and an error 404 is set.
	 *
	 * If access is denied, an error message is returned, and an error 403 is set.
	 *
	 * @param int $pageUid PID of the page with events for which to create the CSV list, must be > 0
	 *
	 * @return string CSV list of events for the given page or an error message in case of an error
	 */
	public function createAndOutputListOfEvents($pageUid) {
		if ($pageUid <= 0) {
			return $this->addErrorHeaderAndReturnMessage(self::NOT_FOUND);
		}
		if (!$this->canAccessListOfEvents($pageUid)) {
			return $this->addErrorHeaderAndReturnMessage(self::ACCESS_DENIED);
		}

		$this->setContentTypeForEventLists();

		return $this->createListOfEvents($pageUid);
	}

	/**
	 * Retrieves a list of events as CSV, including the header line.
	 *
	 * This function does not do any access checks.
	 *
	 * @param int $pageUid PID of the system folder from which the event records should be exported, must be > 0
	 *
	 * @return string CSV export of the event records on that page
	 */
	public function createListOfEvents($pageUid) {
		/** @var $eventListView Tx_Seminars_Csv_EventListView */
		$eventListView = t3lib_div::makeInstance('Tx_Seminars_Csv_EventListView');
		$eventListView->setPageUid($pageUid);

		return $eventListView->render();
	}

	/**
	 * Checks whether the list of registrations is accessible, ie.
	 * 1. CSV access is allowed for testing purposes, or
	 * 2. the logged-in BE user has read access to the registrations table and
	 *    read access to *all* pages where the registration records of the
	 *    selected event are stored.
	 *
	 * @param int $eventUid UID of the event record for which access should be checked, must be > 0
	 *
	 * @return bool TRUE if the list of registrations may be exported as CSV
	 */
	protected function canAccessListOfRegistrations($eventUid) {
		switch ($this->getTypo3Mode()) {
			case 'BE':
				/** @var $accessCheck Tx_Seminars_Csv_BackEndRegistrationAccessCheck */
				$accessCheck = t3lib_div::makeInstance('Tx_Seminars_Csv_BackEndRegistrationAccessCheck');
				$result = $accessCheck->hasAccess();
				break;
			case 'FE':
				/** @var $accessCheck Tx_Seminars_Csv_FrontEndRegistrationAccessCheck */
				$accessCheck = t3lib_div::makeInstance('Tx_Seminars_Csv_FrontEndRegistrationAccessCheck');

				/** @var $seminar tx_seminars_seminar */
				$seminar = t3lib_div::makeInstance('tx_seminars_seminar', $eventUid);
				$accessCheck->setEvent($seminar);

				$result = $accessCheck->hasAccess();
				break;
			default:
				$result = FALSE;
		}

		return $result;
	}

	/**
	 * Checks whether the logged-in BE user has access to the event list.
	 *
	 * @param int $pageUid PID of the page with events for which to check access, must be >= 0
	 *
	 * @return bool TRUE if the list of events may be exported as CSV, FALSE otherwise
	 */
	protected function canAccessListOfEvents($pageUid) {
		/** @var $accessCheck Tx_Seminars_Csv_BackEndEventAccessCheck */
		$accessCheck = t3lib_div::makeInstance('Tx_Seminars_Csv_BackEndEventAccessCheck');
		$accessCheck->setPageUid($pageUid);

		return $accessCheck->hasAccess();
	}

	/**
	 * Sets the HTTP header: the content type and filename (content disposition) for registration lists.
	 *
	 * @return void
	 */
	private function setContentTypeForRegistrationLists() {
		$this->setPageTypeAndDisposition($this->configuration->getAsString('filenameForRegistrationsCsv'));
	}

	/**
	 * Sets the HTTP header: the content type and filename (content disposition) for event lists.
	 *
	 * @return void
	 */
	private function setContentTypeForEventLists() {
		$this->setPageTypeAndDisposition($this->configuration->getAsString('filenameForEventsCsv'));
	}

	/**
	 * Sets the page's content type to CSV and the page's content disposition to the given filename.
	 *
	 * Adds the data directly to the page header.
	 *
	 * @param string $csvFileName the name for the page which is used as storage name, must not be empty
	 *
	 * @return void
	 */
	private function setPageTypeAndDisposition($csvFileName) {
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Content-type: text/csv; header=present; charset=' . $this->configuration->getAsString('charsetForCsv')
		);
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Content-disposition: attachment; filename=' . $csvFileName
		);
	}

	/**
	 * Adds a status header and returns an error message.
	 *
	 * @param int $errorCode
	 *        the type of error message, must be tx_seminars_pi2::ACCESS_DENIED or tx_seminars_pi2::NOT_FOUND
	 *
	 * @return string the error message belonging to the error code, will not be empty
	 *
	 * @throws InvalidArgumentException
	 */
	private function addErrorHeaderAndReturnMessage($errorCode) {
		switch ($errorCode) {
			case self::ACCESS_DENIED:
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 403 Forbidden');
				$result = $this->translate('message_403');
				break;
			case self::NOT_FOUND:
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
				$result = $this->translate('message_404');
				break;
			default:
				throw new InvalidArgumentException('"' . $errorCode . '" is no legal error code.', 1333292523);
		}

		return $result;
	}

	/**
	 * Checks whether the currently logged-in BE-User is allowed to access the registrations records on the given page.
	 *
	 * @param int $pageUid PID of the page to check the access for, must be >= 0
	 *
	 * @return bool
	 *         TRUE if the currently logged-in BE-User is allowed to access the registrations records,
	 *         FALSE if the user has no access or this function is called in FE mode
	 */
	private function canAccessRegistrationsOnPage($pageUid) {
		switch ($this->getTypo3Mode()) {
			case 'BE':
				/** @var $accessCheck Tx_Seminars_Csv_BackEndRegistrationAccessCheck */
				$accessCheck = t3lib_div::makeInstance('Tx_Seminars_Csv_BackEndRegistrationAccessCheck');
				$accessCheck->setPageUid($pageUid);
				$result = $accessCheck->hasAccess();
				break;
			case 'FE':
				// The fall-through is intentional.
			default:
				$result = FALSE;
		}

		return $result;
	}

	/**
	 * Returns the mode currently set in TYPO3_MODE.
	 *
	 * @return string either "FE" or "BE" representing the TYPO3 mode
	 */
	private function getTypo3Mode() {
		if ($this->typo3Mode !== '') {
			return $this->typo3Mode;
		}

		return TYPO3_MODE;
	}

	/**
	 * Sets the TYPO3_MODE.
	 *
	 * The value is stored in the member variable $this->typo3Mode
	 *
	 * This function is for testing purposes only!
	 *
	 * @param string $typo3Mode the TYPO3_MODE to set, must be "BE" or "FE"
	 *
	 * @return void
	 */
	public function setTypo3Mode($typo3Mode) {
		$this->typo3Mode = $typo3Mode;
	}

	/**
	 * Checks whether the currently logged in BE-User has access to the given
	 * event and its registrations.
	 *
	 * Stores the type of the error in $this->errorType
	 *
	 * @param int $eventUid
	 *        the event to check the access for, must be >= 0 but not necessarily point to an existing event
	 *
	 * @return bool TRUE if the event record exists and the BE-User has
	 *                 access to the registrations belonging to the event,
	 *                 FALSE otherwise
	 */
	private function hasAccessToEventAndItsRegistrations($eventUid) {
		$result = FALSE;

		if (!tx_seminars_OldModel_Abstract::recordExists($eventUid, 'tx_seminars_seminars')) {
			$this->errorType = self::NOT_FOUND;
		} elseif (!$this->canAccessListOfRegistrations($eventUid)) {
			$this->errorType = self::ACCESS_DENIED;
		} else {
			$result = TRUE;
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']);
}