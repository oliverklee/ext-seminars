<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_dbplugin' for the 'seminars' extension.
 *
 * It defines the database table names, provides the configuration
 * and calles the base class init functions.
 *
 * This is an abstract class; don't instantiate it.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_configcheck.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_salutationswitcher.php');

// If we are in the back end, we include the extension's locallang.xml.
if ((TYPO3_MODE == 'BE') && is_object($LANG)) {
    $LANG->includeLLFile('EXT:seminars/locallang.xml');
}

class tx_seminars_dbplugin extends tx_seminars_salutationswitcher {
	/** The extension key. */
	var $extKey = 'seminars';

	/** whether init() already has been called (in order to avoid double calls) */
	var $isInitialized = false;

	// Database table names. Will be initialized (indirectly) by $this->init.
	var $tableSeminars;
	var $tableVipsMM;
	var $tableSpeakers;
	var $tableSpeakersMM;
	var $tablePartnersMM;
	var $tableTutorsMM;
	var $tableLeadersMM;
	var $tableSites;
	var $tableSitesMM;
	var $tableOrganizers;
	var $tableAttendances;
	var $tablePaymentMethods;
	var $tableEventTypes;
	var $tableCheckboxes;
	var $tableSeminarsCheckboxesMM;
	var $tableAttendancesCheckboxesMM;
	var $tableLodgings;
	var $tableSeminarsLodgingsMM;
	var $tableAttendancesLodgingsMM;
	var $tableFoods;
	var $tableSeminarsFoodsMM;
	var $tableAttendancesFoodsMM;

	// Constants for the types of records
	var $recordTypeComplete;
	var $recordTypeTopic;
	var $recordTypeDate;

	/** The frontend user who currently is logged in. */
	var $feuser = null;

	/** The configuration check object that will check this object. */
	var $configurationCheck;

	/**
	 * Dummy constructor: Does nothing.
	 *
	 * The base classe's constructor is called in $this->init().
	 */
	function tx_seminars_dbplugin() {
	}

	/**
	 * Initializes the FE plugin stuff, read the configuration
	 * and set the table names while we're at it.
	 *
	 * It is harmless if this function gets called multiple times as it recognizes
	 * this and ignores all calls but the first one.
	 *
	 * This is merely a convenience function.
	 *
	 * If the parameter is ommited, the configuration for plugin.tx_seminar is used instead.
	 *
 	 * @param	array		TypoScript configuration for the plugin
	 *
	 * @access	protected
	 */
	function init($conf = null) {
		if (!$this->isInitialized) {
			if ($GLOBALS['TSFE'] && !isset($GLOBALS['TSFE']->config['config'])) {
				$GLOBALS['TSFE']->config['config'] = array();
			}

			// call the base classe's constructor manually as this isn't done automatically
			parent::tslib_pibase();

			if ($conf !== null) {
				$this->conf = $conf;
			} else {
				// We need to create our own template setup if we are in the BE
				// and we aren't currently creating a DirectMail page.
				if ((TYPO3_MODE == 'BE') && !is_object($GLOBALS['TSFE'])) {
					$template = t3lib_div::makeInstance('t3lib_TStemplate');
					// do not log time-performance information
					$template->tt_track = 0;
					$template->init();

					// Get the root line
					$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
					// the selected page in the BE is found
					// exactly as in t3lib_SCbase::init()
					$rootline = $sys_page->getRootLine(intval(t3lib_div::_GP('id')));

					// This generates the constants/config + hierarchy info for the template.
					$template->runThroughTemplates($rootline, 0);
					$template->generateConfig();

					$this->conf = $template->setup['plugin.']['tx_'.$this->extKey.'.'];
				} else {
					// On the front end, we can use the provided template setup.
					$this->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$this->extKey.'.'];
				}
			}

			$this->pi_setPiVarDefaults();
			$this->pi_loadLL();

			$this->setTableNames();
			$this->setRecordTypes();

			// unserialize the configuration array
			$globalConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']);

			if ($globalConfiguration['enableConfigCheck']) {
				$configurationCheckClassname = t3lib_div::makeInstanceClassName('tx_seminars_configcheck');
				$this->configurationCheck =& new $configurationCheckClassname($this);
			} else {
				$this->configurationCheck = null;
			}

			$this->isInitialized = true;
		}

		return;
	}

	/**
	 * Sets the table names.
	 *
	 * @access	private
	 */
	function setTableNames() {
		$dbPrefix = 'tx_'.$this->extKey.'_';

		$this->tableSeminars       = $dbPrefix.'seminars';
		$this->tableSpeakers       = $dbPrefix.'speakers';
		$this->tableSites          = $dbPrefix.'sites';
		$this->tableOrganizers     = $dbPrefix.'organizers';
		$this->tableAttendances    = $dbPrefix.'attendances';
		$this->tablePaymentMethods = $dbPrefix.'payment_methods';
		$this->tableEventTypes     = $dbPrefix.'event_types';
		$this->tableCheckboxes     = $dbPrefix.'checkboxes';
		$this->tableLodgings       = $dbPrefix.'lodgings';
		$this->tableFoods          = $dbPrefix.'foods';

		$this->tableVipsMM                  = $dbPrefix.'seminars_feusers_mm';
		$this->tableSpeakersMM              = $dbPrefix.'seminars_speakers_mm';
		$this->tablePartnersMM              = $dbPrefix.'seminars_speakers_mm_partners';
		$this->tableTutorsMM                = $dbPrefix.'seminars_speakers_mm_tutors';
		$this->tableLeadersMM               = $dbPrefix.'seminars_speakers_mm_leaders';
		$this->tableSitesMM                 = $dbPrefix.'seminars_place_mm';
		$this->tableSeminarsCheckboxesMM    = $dbPrefix.'seminars_checkboxes_mm';
		$this->tableAttendancesCheckboxesMM = $dbPrefix.'attendances_checkboxes_mm';
		$this->tableSeminarsLodgingsMM      = $dbPrefix.'seminars_lodgings_mm';
		$this->tableAttendancesLodgingsMM   = $dbPrefix.'attendances_lodgings_mm';
		$this->tableSeminarsFoodsMM         = $dbPrefix.'seminars_foods_mm';
		$this->tableAttendancesFoodsMM      = $dbPrefix.'attendances_foods_mm';

		return;
	}

	/**
	 * Sets the record types.
	 *
	 * @access	private
	 */
	function setRecordTypes() {
		$this->recordTypeComplete	= 0;
		$this->recordTypeTopic		= 1;
		$this->recordTypeDate		= 2;

		return;
	}

	/**
	 * Gets a value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS setup,
	 * an empty string is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 * @param	string		whether this is a filename, which has to be combined with a path
	 *
	 * @return	string		the value of the corresponding flexforms or TS setup entry (may be empty)
	 *
	 * @access	private
	 */
	function getConfValue($fieldName, $sheet = 'sDEF', $isFileName = false) {
		$flexformsValue = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $fieldName, $sheet);
		if ($isFileName && !empty($flexformsValue)) {
			$flexformsValue = $this->addPathToFileName($flexformsValue);
		}
		$confValue = isset($this->conf[$fieldName]) ? $this->conf[$fieldName] : '';

		return ($flexformsValue) ? $flexformsValue : $confValue;
	}

	/**
	 * Adds a path in front of the file name.
	 * This is used for files that are selected in the Flexform of the front end plugin.
	 *
	 * If no path is provided, the default (uploads/[extension_name]/) is used as path.
	 *
	 * An example (default, with no path provided):
	 * If the file is named 'template.tmpl', the output will be 'uploads/[extension_name]/template.tmpl'.
	 * The '[extension_name]' will be replaced by the name of the calling extension.
	 *
	 * @param	string		the file name
	 * @param	string		the path to the file (without filename), must contain a slash at the end, may contain a slash at the beginning (if not relative)
	 *
	 * @return	string		the complete path including file name
	 *
	 * @access	private
	 */
	function addPathToFileName($fileName, $path = '') {
		if (empty($path)) {
			$path = 'uploads/tx_'.$this->extKey.'/';
		}

		return $path.$fileName;
	}

	/**
	 * Gets a trimmed string value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS setup,
	 * an empty string is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 * @param	string		whether this is a filename, which has to be combined with a path
	 *
	 * @return	string		the trimmed value of the corresponding flexforms or TS setup entry (may be empty)
	 *
	 * @access	public
	 */
	function getConfValueString($fieldName, $sheet = 'sDEF', $isFileName = false) {
		return trim($this->getConfValue($fieldName, $sheet, $isFileName));
	}

	/**
	 * Checks whether a string value from flexforms or TS setup is set.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is checked. If there is no field with that name in TS setup,
	 * false is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 *
	 * @return	boolean		whether there is a non-empty value in the corresponding flexforms or TS setup entry
	 *
	 * @access	public
	 */
	function hasConfValueString($fieldName, $sheet = 'sDEF') {
		return ($this->getConfValueString($fieldName, $sheet) != '');
	}

	/**
	 * Gets an integer value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS setup,
	 * zero is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 *
	 * @return	integer		the inval'ed value of the corresponding flexforms or TS setup entry
	 *
	 * @access	public
	 */
	function getConfValueInteger($fieldName, $sheet = 'sDEF') {
		return intval($this->getConfValue($fieldName, $sheet));
	}

	/**
	 * Checks whether an integer value from flexforms or TS setup is set and non-zero.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is checked. If there is no field with that name in TS setup,
	 * false is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 *
	 * @return	boolean		whether there is a non-zero value in the corresponding flexforms or TS setup entry
	 *
	 * @access	public
	 */
	function hasConfValueInteger($fieldName, $sheet = 'sDEF') {
		return (boolean) $this->getConfValueInteger($fieldName, $sheet);
	}

	/**
	 * Gets a boolean value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS setup,
	 * false is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 *
	 * @return	boolean		the boolean value of the corresponding flexforms or TS setup entry
	 *
	 * @access	public
	 */
	function getConfValueBoolean($fieldName, $sheet = 'sDEF') {
		return (boolean) $this->getConfValue($fieldName, $sheet);
	}

	/**
	 * Extracts a value within listView.
	 *
	 * @param	string		TS setup field name to extract (within listView.), must not be empty
	 *
	 * @return	string		the trimmed contents of that field within listView. (may be empty)
	 *
	 * @access	public
	 */
	function getListViewConfValueString($fieldName) {
		$result = '';
		if (isset($this->conf['listView.'])
			&& isset($this->conf['listView.'][$fieldName])) {
			$result = trim($this->conf['listView.'][$fieldName]);
		}

		return $result;
	}

	/**
	 * Checks whether a front end user is logged in.
	 *
	 * @return	boolean		true if a user is logged in, false otherwise
	 *
	 * @access	public
	 */
	function isLoggedIn() {
		return ((boolean) $GLOBALS['TSFE']) && ((boolean) $GLOBALS['TSFE']->loginUser);
	}

	/**
	 * If a user logged in, retrieves that user's data as stored in the
	 * table "feusers" and stores it in $this->feuser.
	 *
	 * If no user is logged in, $this->feuser will be null.
	 *
	 * @access	private
	 */
	function retrieveFEUser() {
		$this->feuser = $this->isLoggedIn() ? $GLOBALS['TSFE']->fe_user->user : null;
	}

	/**
	 * Returns the UID of the currently logged-in FE user
	 * or 0 if no FE user is logged in.
	 *
	 * @return	integer		the UID of the logged-in FE user or 0 if no FE user is logged in
	 *
	 * @access	public
	 */
	function getFeUserUid() {
		// If we don't have the FE user's UID (yet), try to retrieve it.
		if (!$this->feuser) {
			$this->retrieveFEUser();
		}

		return ($this->isLoggedIn() ? intval($this->feuser['uid']) : 0);
	}

	/**
	 * Sets the "flavor" of the object to check.
	 *
	 * @param	string		a short string identifying the "flavor" of the object to check (may be empty)
	 *
	 * @access	public
	 */
	function setFlavor($flavor) {
		if ($this->configurationCheck) {
			$this->configurationCheck->setFlavor($flavor);
		}

		return;
	}

	/**
	 * Returns the current flavor of the object to check.
	 *
	 * @return	string		the current flavor of the object to check (or an empty string if no flavor is set)
	 *
	 * @access	public
	 */
	function getFlavor() {
		$result = '';

		if ($this->configurationCheck) {
			$result = $this->configurationCheck->getFlavor();
		}

		return $result;
	}

	/**
	 * Sets the error text of $this->configurationCheck.
	 *
	 * If this->configurationCheck is null, this function is a no-op.
	 *
	 * @param	string		error text to set (may be empty)
	 *
	 * @access	protected
	 */
	function setErrorMessage($message) {
		if ($this->configurationCheck) {
			$this->configurationCheck->setErrorMessage($message);
		}

		return;
	}

	/**
	 * Checks this object's configuration and returns a formatted error message
	 * (if any). If there are several objects of this class, still only one
	 * error message is created (in order to prevent duplicate messages).
	 *
	 * @param	boolean		whether to use the raw message instead of the wrapped message
	 * @param	string		flavor to use temporarily for this call (leave empty to not change the flavor)
	 *
	 * @return	string		a formatted error message (if there are errors) or an empty string
	 *
	 * @access	public
	 */
	function checkConfiguration($useRawMessage = false, $temporaryFlavor = '') {
		static $hasDisplayedMessage = false;
		$result = '';

		if ($this->configurationCheck) {
			if (!empty($temporaryFlavor)) {
				$oldFlavor = $this->getFlavor();
				$this->setFlavor($temporaryFlavor);
			}

			$message = ($useRawMessage) ?
				$this->configurationCheck->checkIt() :
				$this->configurationCheck->checkItAndWrapIt();

			if (!empty($temporaryFlavor)) {
				$this->setFlavor($oldFlavor);
			}

			// If we have a message, only return it if it is the first message
			// for objects of this class.
			if (!empty($message) && !$hasDisplayedMessage) {
				$result = $message;
				$hasDisplayedMessage = true;
			}
		}

		return $result;
	}

	/**
	 * Returns an empty string if there are no configuration errors.
	 * Otherwise, returns the wrapped error text.
	 *
	 * Use this method if you want to display this message pretty
	 * directly and it doesn't need to get handled to other configcheck
	 * objects.
	 *
	 * @return	string		the wrapped error text (or an empty string if there are no errors)
	 *
	 * @access	protected
	 */
	function getWrappedConfigCheckMessage() {
		$result = '';

		if ($this->configurationCheck) {
			$result = $this->configurationCheck->getWrappedMessage();
		}

		return $result;
	}

	/**
	 * Provides data items from the DB.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 * @param	string		the table name to query
	 * @param	string		query parameter that will be used as the WHERE clause (may be omitted)
	 *
	 * @return	array		$items with additional items from the $params['what'] table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateList($items, $tableName, $queryParameter = '1=1') {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$tableName,
				$queryParameter
				.t3lib_pageSelect::enableFields($tableName),
			'',
			'title',
			'');

		if ($dbResult) {
			while ($dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$uid = $dbResultRow['uid'];
				$items[$uid] = array(
					'caption'	=> $dbResultRow['title'],
					'value'		=> $uid
				);
			}
		}

		// Reset the array pointer as the populateList* functions expect
		// arrays with a reset array pointer.
		reset($items);

		return $items;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_dbplugin.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_dbplugin.php']);
}

?>
