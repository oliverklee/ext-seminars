<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2011 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(PATH_typo3 . 'template.php');
if (is_object($LANG)) {
	$LANG->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
}

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('seminars') . 'tx_seminars_modifiedSystemTables.php');

/**
 * Plugin 'CSV export' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_pi2 extends tx_oelib_templatehelper {
	/**
	 * @var integer HTTP status code for "page not found"
	 */
	const NOT_FOUND = 404;

	/**
	 * @var integer HTTP status code for "access denied"
	 */
	const ACCESS_DENIED = 403;

	/**
	 * @var integer the depth of the recursion for the back-end pages
	 */
	const RECURSION_DEPTH = 250;

	/**
	 * @var string export mode for attachments created from back end
	 */
	const EXPORT_MODE_WEB = 'web';

	/**
	 * @var string export mode for attachments send via e-mail
	 */
	const EXPORT_MODE_EMAIL = 'e-mail';

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
	 * @var tx_seminars_configgetter This object provides access to config
	 * values in plugin.tx_seminars.
	 */
	private $configGetter = NULL;

	/**
	 * @var string the TYPO3 mode set for testing purposes
	 */
	private $typo3Mode;

	/**
	 * @var integer the HTTP status code of error
	 */
	private $errorType = 0;

	/**
	 * @var string the export mode for the CSV file possible values are
	 *             EXPORT_MODE_WEB and EXPORT_MODE_WEB
	 */
	private $exportMode = self::EXPORT_MODE_WEB;

	/**
	 * @var language the language object for translating the CSV headings
	 */
	private $language = NULL;

	/**
	 * The constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->loadLocallangFiles();
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->configGetter) {
			$this->configGetter->__destruct();
			unset($this->configGetter);
		}
		unset($this->language);

		parent::__destruct();
	}

	/**
	 * Creates a CSV export.
	 *
	 * @param string (unused)
	 * @param array TypoScript configuration for the plugin, may be empty
	 *
	 * @return string HTML for the plugin, might be empty
	 */
	public function main($unused, array $configuration) {
		try {
			$this->init($configuration);

			switch ($this->piVars['table']) {
				case 'tx_seminars_seminars':
					$result = $this->createAndOutputListOfEvents(
						intval($this->piVars['pid'])
					);
					break;
				case 'tx_seminars_attendances':
					$result = $this->createAndOutputListOfRegistrations(
						intval($this->piVars['eventUid'])
					);
					break;
				default:
					$result = $this->addErrorHeaderAndReturnMessage(
						self::NOT_FOUND
					);
					break;
			}

			$dataCharset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']
				? strtolower($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'])
				: 'iso-8859-15';
			$resultCharset = strtolower(
				$this->configGetter->getConfValueString('charsetForCsv')
			);
			if ($dataCharset !== $resultCharset) {
				$result = $this->getCharsetConversion()->conv(
					$result, $dataCharset, $resultCharset
				);
			}
		} catch (Exception $exception) {
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
				'Status: 500 Internal Server Error'
			);
			$result = $exception->getMessage() . LF . LF .
				$exception->getTraceAsString() . LF . LF;
		}

		return $result;
	}

	/**
	 * Initializes this object and its configuration getter.
	 *
	 * @param array TypoScript configuration for the plugin, may be empty
	 */
	public function init(array $configuration = array()) {
		parent::init($configuration);

		if ($this->configGetter === NULL) {
			$this->configGetter = tx_oelib_ObjectFactory::make(
				'tx_seminars_configgetter'
			);
			$this->configGetter->init();
		}
	}

	/**
	 * Retrieves an active charset conversion instance.
	 *
	 * @return t3lib_cs a charset converstion instance
	 */
	protected function getCharsetConversion() {
		if (isset($GLOBALS['TSFE'])) {
			$instance = $GLOBALS['TSFE']->csConvObj;
		} elseif (isset($GLOBALS['LANG'])) {
			$instance = $GLOBALS['LANG']->csConvObj;
		} else {
			throw new Exception(
				'There was neither a front end nor a back end detected.'
			);
		}

		return $instance;
	}

	/**
	 * Loads the locallang files needed to translate the CSV headings.
	 */
	private function loadLocallangFiles() {
		if (is_object($GLOBALS['TSFE']) && is_array($this->LOCAL_LANG)) {
			$this->language = tx_oelib_ObjectFactory::make('language');
			if (!empty($this->LLkey)) {
				$this->language->init($this->LLkey);
			}
		} elseif (is_object($GLOBALS['LANG'])) {
			$this->language = $GLOBALS['LANG'];
		} else {
			throw new Exception(
				'The language could not be loaded. Please check your installation.'
			);
		}

		$this->language->includeLLFile(
			t3lib_extMgm::extPath('seminars') . 'locallang_db.xml'
		);
		$this->language->includeLLFile(
			t3lib_extMgm::extPath('lang') . 'locallang_general.xml'
		);
	}

	/**
	 * Creates a CSV list of registrations for the event given in $eventUid,
	 * including a heading line.
	 *
	 * If the seminar does not exist, an error message is returned, and an error
	 * 404 is set.
	 *
	 * If access is denied, an error message is returned, and an error 403 is
	 * set.
	 *
	 * @param integer UID of the event for which to create the CSV list, must be >= 0
	 *
	 * @return string CSV list of registrations for the given seminar or
	 *                an error message in case of an error
	 */
	public function createAndOutputListOfRegistrations($eventUid = 0) {
		$pid = intval($this->piVars['pid']);
		if ($eventUid > 0) {
			if (!$this->hasAccessToEventAndItsRegistrations($eventUid)) {
				return $this->addErrorHeaderAndReturnMessage($this->errorType);
			}
		} else {
			if (!$this->canAccessRegistrationsOnPage($pid)) {
				return $this->addErrorHeaderAndReturnMessage(
					self::ACCESS_DENIED
				);
			}
		}

		$this->setContentTypeForRegistrationLists();
		if ($eventUid == 0) {
			$result = $this->createListOfRegistrationsOnPage($pid);
		} else {
			$result = $this->createListOfRegistrations($eventUid);
		}

		return $result;
	}

	/**
	 * Creates a CSV list of registrations for the event with the UID given in
	 * $eventUid, including a heading line.
	 *
	 * This function does not do any access checks.
	 *
	 * @param integer UID of the event for which the registration list
	 *                should be created, must be > 0
	 *
	 * @return string CSV list of registrations for the given seminar or an
	 *                empty string if there is not event with the provided UID
	 */
	public function createListOfRegistrations($eventUid) {
		if (!tx_seminars_OldModel_Abstract::recordExists(
			$eventUid, 'tx_seminars_seminars'
		)) {
			return '';
		}

		$registrationBagBuilder = $this->createRegistrationBagBuilder();
		$registrationBagBuilder->limitToEvent($eventUid);

		return $this->createRegistrationsHeading() .
			$this->getRegistrationsCsvList($registrationBagBuilder);
	}

	/**
	 * Returns the list of registrations as CSV separated values.
	 *
	 * The fields are separated by semicolons and the lines by CRLF.
	 *
	 * @param tx_seminars_BagBuilder_Registration $builder
	 *        the bag builder already limited to the registrations which should
	 *        be returned
	 *
	 * @return string the list of registrations, will be empty if no
	 *                registrations have been given
	 */
	private function getRegistrationsCsvList(
		tx_seminars_BagBuilder_Registration $builder
	) {
		$result = '';
		$bag = $builder->build();

		foreach ($bag as $registration) {
			switch ($this->getTypo3Mode()) {
				case 'BE':
					$hasAccess = $GLOBALS['BE_USER']->doesUserHaveAccess(
						t3lib_BEfunc::getRecord(
							'pages', $registration->getPageUid()),
						1
					);
					break;
				case 'FE':
					$hasAccess = TRUE;
					break;
				default:
					throw new Exception('You are trying to get a CSV list on a ' .
						'non supported mode. Currently only BackEnd and ' .
						'FrontEnd mode are allowed.'
					);
			}

			if ($hasAccess) {
				$userData = $this->retrieveData(
					$registration,
					'getUserData',
					$this->getFrontEndUserFieldsConfiguration()
				);
				$registrationData = $this->retrieveData(
					$registration,
					'getRegistrationData',
					$this->getRegistrationFieldsConfiguration()
				);
				// Combines the arrays with the user and registration data
				// and creates a list of semicolon-separated values from them.
				$result .= implode(
					';', array_merge($userData, $registrationData)
				) . CRLF;
			}
		}

		$bag->__destruct();

		return $result;
	}

	/**
	 * Creates the heading line for the list of registrations (including a CRLF
	 * at the end).
	 *
	 * @return string the heading line for the list of registrations, will
	 *                not be empty
	 */
	protected function createRegistrationsHeading() {
		$fieldsFromFeUser = $this->localizeCsvHeadings(
			t3lib_div::trimExplode(
				',', $this->getFrontEndUserFieldsConfiguration(), TRUE
			),
			'LGL'
		);
		$fieldsFromAttendances = $this->localizeCsvHeadings(
			t3lib_div::trimExplode(
				',', $this->getRegistrationFieldsConfiguration(), TRUE
			),
			'tx_seminars_attendances'
		);

		$result = array_merge($fieldsFromFeUser, $fieldsFromAttendances);

		return implode(';', $result) . CRLF;
	}

	/**
	 * Creates a CSV list of events for the page given in $pid.
	 *
	 * If the page does not exist, an error message is returned, and an error
	 * 404 is set.
	 *
	 * If access is denied, an error message is returned, and an error 403 is
	 * set.
	 *
	 * @param integer PID of the page with events for which to create the CSV
	 *                list, must be > 0
	 *
	 * @return string CSV list of events for the given page or an error
	 *                message in case of an error
	 */
	public function createAndOutputListOfEvents($pid) {
		if ($pid > 0) {
			if ($this->canAccessListOfEvents($pid)) {
				$this->setContentTypeForEventLists();
				$result = $this->createListOfEvents($pid);
			} else {
				$result = $this->addErrorHeaderAndReturnMessage(
					self::ACCESS_DENIED
				);
			}
		} else {
			$result = $this->addErrorHeaderAndReturnMessage(self::NOT_FOUND);
		}

		return $result;
	}

	/**
	 * Retrieves a list of events as CSV, including the header line.
	 *
	 * This function does not do any access checks.
	 *
	 * @param integer PID of the system folder from which the event
	 * records should be exported, must be > 0
	 *
	 * @return string CSV export of the event records on that page
	 */
	public function createListOfEvents($pid) {
		if ($pid <= 0) {
			return '';
		}

		$result = $this->createEventsHeading();

		$builder = tx_oelib_ObjectFactory::make('tx_seminars_BagBuilder_Event');
		$builder->setBackEndMode();
		$builder->setSourcePages($pid, 255);

		foreach ($builder->build() as $seminar) {
			$seminarData = $this->retrieveData(
				$seminar,
				'getEventData',
				$this->configGetter->getConfValueString(
					'fieldsFromEventsForCsv'
				)
			);
			// Creates a list of comma-separated values of the event data.
			$result .= implode(';', $seminarData) . CRLF;
		}

		return $result;
	}

	/**
	 * Creates the heading line for a CSV event list.
	 *
	 * @return string header list, will not be empty if the CSV export has been
	 *                configured correctly
	 */
	private function createEventsHeading() {
		$eventFields = t3lib_div::trimExplode(
			',',
			$this->configGetter->getConfValueString('fieldsFromEventsForCsv'),
			TRUE
		);

		return implode(
			';', $this->localizeCsvHeadings($eventFields, 'tx_seminars_seminars')
		) . CRLF;
	}

	/**
	 * Retrieves data from an object and returns that data as an array of
	 * values. The individual values are already wrapped in double quotes, with
	 * the contents having all quotes escaped.
	 *
	 * @param object object that will deliver the data
	 * @param string name of a function of the given object that expects
	 * a key as a parameter and returns the value for that
	 * key as a string
	 * @param string comma-separated list of keys to retrieve
	 *
	 * @return array the data for the keys provided in $keys
	 * (may be empty)
	 */
	protected function retrieveData($dataSupplier, $supplierFunction, $keys) {
		$result = array();

		if (($keys != '') && method_exists($dataSupplier, $supplierFunction)) {
			$allKeys = t3lib_div::trimExplode(',', $keys);
			foreach ($allKeys as $currentKey) {
				$rawData = $dataSupplier->$supplierFunction($currentKey);
				// Escapes double quotes and wraps the whole string in double
				// quotes.
				if (strpos($rawData, '"') !== FALSE) {
					$result[] = '"'.str_replace('"', '""', $rawData).'"';
				} elseif ((strpos($rawData, ';') !== FALSE) ||
					(strpos($rawData, LF) !== FALSE)
				){
					$result[] = '"' . $rawData . '"';
				} else {
					$result[] = $rawData;
				}
			}
		}

		return $result;
	}

	/**
	 * Checks whether the list of registrations is accessible, ie.
	 * 1. CSV access is allowed for testing purposes, or
	 * 2. the logged-in BE user has read access to the registrations table and
	 *    read access to *all* pages where the registration records of the
	 *    selected event are stored.
	 *
	 * TODO: When additional ways to access the CSV data are added (e.g. FE
	 * links), the corresponding access checks need to be added to this
	 * function.
	 *
	 * @param integer UID of the event record for which access should be
	 *                checked, must be > 0
	 *
	 * @return boolean TRUE if the list of registrations may be exported as CSV
	 */
	public function canAccessListOfRegistrations($eventUid) {
		// no need to check any special permissions if global access is granted
		if ($this->configGetter->getConfValueBoolean('allowAccessToCsv')) {
			return TRUE;
		}

		switch ($this->getTypo3Mode()) {
			case 'BE':
				// Checks read access to the registrations table.
				$result = $GLOBALS['BE_USER']->check(
					'tables_select',
					'tx_seminars_attendances'
				);
				// Checks read access to all pages with registrations from the
				// selected event.
				$pidArray = tx_oelib_db::selectMultiple(
					'DISTINCT pid',
					'tx_seminars_attendances',
					'seminar=' . $eventUid .
						tx_oelib_db::enableFields('tx_seminars_attendances')
				);
				foreach ($pidArray as $pid) {
					// Checks read access for the current page.
					$result = $result && $GLOBALS['BE_USER']->doesUserHaveAccess(
						t3lib_BEfunc::getRecord('pages', $pid['pid']), 1
					);
				}
				break;
			case 'FE':
				$seminar = tx_oelib_ObjectFactory::make(
					'tx_seminars_seminar', $eventUid
				);

				$pi1TypoScriptSetup
					=& $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_seminars_pi1.'];

				$isCsvExportOfRegistrationsInMyVipEventsViewAllowed
					= (boolean) $pi1TypoScriptSetup['allowCsvExportOfRegistrationsInMyVipEventsView'];

				$result = $isCsvExportOfRegistrationsInMyVipEventsViewAllowed
					&& $seminar->isUserVip(
						$this->getFeUserUid(),
						$pi1TypoScriptSetup['defaultEventVipsFeGroupID']
					);
				break;
			default:
				$result = FALSE;
				break;
		}

		return $result;
	}

	/**
	 * Checks whether the logged-in BE user has read access to the events table
	 * and read access to the page with the given PID.
	 *
	 * @param integer $pid
	 *        PID of the page with events for which to check access, must
	 *        be >= 0
	 *
	 * @return boolean TRUE if the list of events may be exported as CSV, FALSE
	 *                 otherwise
	 */
	public function canAccessListOfEvents($pid) {
		return $this->canAccessTableAndPage('tx_seminars_seminars', $pid);
	}

	/**
	 * Sets the HTTP header: the content type and filename (content disposition)
	 * for registration lists.
	 */
	private function setContentTypeForRegistrationLists() {
		$this->setPageTypeAndDisposition(
			$this->configGetter->getConfValueString(
				'filenameForRegistrationsCsv'
			)
		);
	}

	/**
	 * Sets the HTTP header: the content type and filename (content disposition)
	 * for event lists.
	 */
	private function setContentTypeForEventLists() {
		$this->setPageTypeAndDisposition(
			$this->configGetter->getConfValueString('filenameForEventsCsv')
		);
	}

	/**
	 * Sets the page's content type to CSV and the page's content disposition to
	 * the given filename.
	 *
	 * Adds the data directly to the page header.
	 *
	 * @param string $csvFileName
	 *        the name for the page which is used as storage name, must not be
	 *        empty
	 */
	private function setPageTypeAndDisposition($csvFileName) {
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Content-type: text/csv; header=present; charset=' .
				$this->configGetter->getConfValueString('charsetForCsv')
		);
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Content-disposition: attachment; filename=' . $csvFileName
		);
	}

	/**
	 * Returns our config getter (which might be NULL if we aren't initialized
	 * properly yet).
	 *
	 * This function is intended for testing purposes only.
	 *
	 * @return object our config getter, might be NULL
	 */
	public function getConfigGetter() {
		return $this->configGetter;
	}

	/**
	 * Returns the typeNum from the TypoScript setup in tx_seminars_pi2.typeNum.
	 *
	 * @return integer the typeNum in tx_seminars_pi2.typeNum
	 */
	public static function getTypeNum() {
		return intval(
			$GLOBALS['TSFE']->tmpl->setup['tx_seminars_pi2.']['typeNum']
		);
	}

	/**
	 * Adds a status header and returns an error message.
	 *
	 * @param integer $errorCode
	 *        the type of error message, must be tx_seminars_pi2::ACCESS_DENIED
	 *        or tx_seminars_pi2::NOT_FOUND
	 *
	 * @return string the error message belonging to the error code, will not be
	 *                empty
	 */
	private function addErrorHeaderAndReturnMessage($errorCode) {
		switch ($errorCode) {
			case self::ACCESS_DENIED:
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
					'Status: 403 Forbidden'
				);
				$result = $this->translate('message_403');
				break;
			case self::NOT_FOUND:
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
					'Status: 404 Not Found'
				);
				$result = $this->translate('message_404');
				break;
			default:
				throw new Exception(
					'"' . $errorCode . '" is no legal error code.'
				);
				break;
		}

		return $result;
	}

	/**
	 * Checks whether the currently logged-in BE-User is allowed to access the
	 * registrations records on the given page.
	 *
	 * @param integer $pid PID of the page to check the access for, must be >= 0
	 *
	 * @return booelan TRUE if the currently logged-in BE-User is allowed to
	 *                 access the registrations records, FALSE if the user has
	 *                 no access or this function is called in FE mode
	 */
	private function canAccessRegistrationsOnPage($pid) {
		return $this->canAccessTableAndPage('tx_seminars_attendances', $pid);
	}

	/**
	 * Checks whether the currently logged-in BE-User is allowed to access the
	 * given table and page.
	 *
	 * @param string $table
	 *        the name of the table to check the read access for, must not be
	 *        empty
	 *
	 * @param integer $pid the page to check the access for, must be >= 0
	 *
	 * @return boolean TRUE if the user has access to the given table and page,
	 *                 FALSE otherwise, will also return FALSE if this function
	 *                 is called in any other TYPO3 mode than BE
	 */
	private function canAccessTableAndPage($table, $pid) {
		$result = FALSE;

		if ($this->getTypo3Mode() == 'BE') {
			// Checks read access to the given table.
			$result = $GLOBALS['BE_USER']->check(
				'tables_select', $table
			);
			// Checks read access to the given page.
			$result = $result && $GLOBALS['BE_USER']->doesUserHaveAccess(
				t3lib_BEfunc::getRecord('pages', $pid), 1
			);
		}

		return $result;
	}

	/**
	 * Creates a CSV list of registrations for the given page and its subpages,
	 * including a heading line.
	 *
	 * @param integer $pid
	 *        the PID of the page to export the registrations for, must be >= 0
	 *
	 * @return string CSV list of registrations for the given page, will be
	 *                empty if no registrations could be found on the given page
	 *                and its subpages
	 */
	private function createListOfRegistrationsOnPage($pid) {
		$registrationsBagBuilder = $this->createRegistrationBagBuilder();
		$registrationsBagBuilder->setSourcePages($pid, self::RECURSION_DEPTH);

		return $this->createRegistrationsHeading() .
			$this->getRegistrationsCsvList($registrationsBagBuilder);
	}

	/**
	 * Creates a registrationBagBuilder with some preset limitations.
	 *
	 * @return tx_seminars_BagBuilder_Registration the bag builder with some
	 *                                             preset limitations
	 */
	private function createRegistrationBagBuilder() {
		$registrationBagBuilder = tx_oelib_ObjectFactory::make(
			'tx_seminars_BagBuilder_Registration'
		);

		if (!$this->getRegistrationsOnQueueConfiguration()) {
			$registrationBagBuilder->limitToRegular();
		}

		$registrationBagBuilder->limitToExistingUsers();

		return $registrationBagBuilder;
	}

	/**
	 * Returns the mode currently set in TYPO3_MODE.
	 *
	 * @return string either "FE" or "BE" representing the TYPO3 mode
	 */
	private function getTypo3Mode() {
		if ($this->typo3Mode != '') {
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
	 * @param integer $eventUid
	 *        the event to check the access for, must be >= 0 but not
	 *        necessarily point to an existing event
	 *
	 * @return boolean TRUE if the event record exists and the BE-User has
	 *                 access to the registrations belonging to the event,
	 *                 FALSE otherwise
	 */
	private function hasAccessToEventAndItsRegistrations($eventUid) {
		$result = FALSE;

		if (!tx_seminars_OldModel_Abstract::recordExists(
			$eventUid, 'tx_seminars_seminars'
		)) {
			$this->errorType = self::NOT_FOUND;
		} elseif (!$this->canAccessListOfRegistrations($eventUid)) {
			$this->errorType = self::ACCESS_DENIED;
		} else {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Sets the mode of the CSV export.
	 *
	 * @param string $exportMode
	 *        the export mode, must be either tx_seminars_pi2::EXPORT_MODE_WEB or
	 *        tx_seminars_pi2::EXPORT_MODE_EMAIL
	 */
	public function setExportMode($exportMode) {
		$this->exportMode = ($exportMode == self::EXPORT_MODE_EMAIL)
			? self::EXPORT_MODE_EMAIL
			: self::EXPORT_MODE_WEB;
	}

	/**
	 * Gets the fields which should be used from the fe_users table for the CSV
	 * files.
	 *
	 * @return string the fe_user table fields to use in the CSV file, will be
	 *                empty if no fields were set.
	 */
	private function getFrontEndUserFieldsConfiguration() {
		switch ($this->exportMode) {
			case self::EXPORT_MODE_EMAIL:
				$configurationVariable = 'fieldsFromFeUserForEmailCsv';
				break;
			default:
				$configurationVariable = 'fieldsFromFeUserForCsv';
				break;
		}

		return $this->configGetter->getConfValueString($configurationVariable);
	}

	/**
	 * Returns the fields which should be used from the attendances table for
	 * the CSV attachment.
	 *
	 * @return string the attendance table fields to use in the CSV attachment,
	 *                will be empty if no fields were set.
	 */
	private function getRegistrationFieldsConfiguration() {
		switch ($this->exportMode) {
			case self::EXPORT_MODE_EMAIL:
				$configurationVariable = 'fieldsFromAttendanceForEmailCsv';
				break;
			default:
				$configurationVariable = 'fieldsFromAttendanceForCsv';
				break;
		}

		return $this->configGetter->getConfValueString($configurationVariable);
	}

	/**
	 * Returns whether the attendances on queue should also be exported in the
	 * CSV file.
	 *
	 * @return boolean TRUE if the attendances on queue should also be exported,
	 *                 FALSE otherwise
	 */
	private function getRegistrationsOnQueueConfiguration() {
		switch ($this->exportMode) {
			case self::EXPORT_MODE_EMAIL:
				$configurationVariable = 'showAttendancesOnRegistrationQueueInEmailCsv';
				break;
			default:
				$configurationVariable = 'showAttendancesOnRegistrationQueueInCSV';
				break;
		}

		return $this->configGetter->getConfValueBoolean($configurationVariable);
	}

	/**
	 * Returns the localized field names.
	 *
	 * @param array $fieldNames the field names to translate, may be empty
	 * @param string $tableName the table to which the fields belong to
	 *
	 * @return array the translated field names in an array, will be empty if no
	 *               field names were given
	 */
	private function localizeCsvHeadings(array $fieldNames, $tableName) {
		if (empty($fieldNames)) {
			return array();
		}
		$result = array();

		foreach ($fieldNames as $fieldName) {
			$translation = trim($this->language->getLL($tableName . '.' . $fieldName));

			if (substr($translation, -1) == ':') {
				$translation = substr($translation, 0, -1);
			}

			$result[] = $translation;
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']);
}
?>