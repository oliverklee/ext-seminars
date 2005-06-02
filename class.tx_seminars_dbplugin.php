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
 * This abstract class is intended soleley for extension.
 * It defines some common data and functions.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once (t3lib_extMgm :: extPath('salutationswitcher').'class.tx_salutationswitcher.php');

class tx_seminars_dbplugin extends tx_salutationswitcher {
	/** The extension key. */
	var $extKey = 'seminars';

	// Database table names. Will be initialized on object construction. 
	var $tableSeminars;
	var $tableSpeakers;
	var $tableSpeakersMM;
	var $tableSites;
	var $tableSitesMM;
	var $tableOrganizers;
	var $tableAttendances;

	/**
	 * Dummy constructor: Does nothing.
	 * 
	 * The base classe's constructor is called in $this->init().
	 */
	function tx_seminars_dbplugin() {
	}

	/**
	 * Initialize the FE plugin stuff
	 * and set the table names while we're at it.
	 * 
	 * This is merely a convenience function.
	 * 
	 * @param	array		TypoScript configuration
	 * 						(usually the same as for the FE plugin/BE module that instantiates this class)
	 *
	 * @access protected 
	 */
	function init($conf) {
		// call the base classe's constructor manually as this isn't done automatically
		parent :: tslib_pibase();

		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->setTableNames();
	}

	/**
	 * Set the table names.
	 * 
	 * @access private 
	 */
	function setTableNames() {
		$dbPrefix = 'tx_'.$this->extKey.'_';

		$this->tableSeminars = $dbPrefix.'seminars';
		$this->tableSpeakers = $dbPrefix.'speakers';
		$this->tableSites = $dbPrefix.'sites';
		$this->tableOrganizers = $dbPrefix.'organizers';
		$this->tableAttendances = $dbPrefix.'attendances';

		$this->tableSpeakersMM = $dbPrefix.'seminars_speakers_mm';
		$this->tableSitesMM = $dbPrefix.'seminars_place_mm';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_dbplugin.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_dbplugin.php']);
}
