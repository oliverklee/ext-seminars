<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
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
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_salutationswitcher.php');

class tx_seminars_dbplugin extends tx_seminars_salutationswitcher {
	/** The extension key. */
	var $extKey = 'seminars';

	/** whether init() already has been called (in order to avoid double calls) */
	var $isInitialized = false;

	// Database table names. Will be initialized (indirectly) by $this->init.
	var $tableSeminars;
	var $tableSpeakers;
	var $tableSpeakersMM;
	var $tableSites;
	var $tableSitesMM;
	var $tableOrganizers;
	var $tableAttendances;
	var $tablePaymentMethods;

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
			// call the base classe's constructor manually as this isn't done automatically
			parent::tslib_pibase();

			if ($conf !== null) {
				$this->conf = $conf;
			} else {
				if (TYPO3_MODE == 'BE') {
					// On the back end, we need to create our own template setup.
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

		$this->tableSpeakersMM     = $dbPrefix.'seminars_speakers_mm';
		$this->tableSitesMM        = $dbPrefix.'seminars_place_mm';

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
	 *
	 * @return	string		the value of the corresponding flexforms or TS setup entry (may be empty)
	 *
	 * @access	protected
	 */
	function getConfValue($fieldName, $sheet = 'sDEF') {
		$flexformsValue = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $fieldName, $sheet);
		$confValue = isset($this->conf[$fieldName]) ? $this->conf[$fieldName] : '';

		return ($flexformsValue) ? $flexformsValue : $confValue;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_dbplugin.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_dbplugin.php']);
}
