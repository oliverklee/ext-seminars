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
 * Plugin 'Seminar registration' for the 'seminars' extension.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationmanager.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');

class tx_seminars_pi3 extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_pi3';
	/** Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi3/class.tx_seminars_pi3.php';

	/** The seminar for which the user wants to register. */
	var $seminar;
	
	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;
	
	/**
	 * Display the registration plugin HTML.
	 *
	 * @param	string		Default content string, ignore
	 * @param	array		TypoScript configuration for the plugin
	 * 
	 * @return	string		HTML for the plugin
	 * 
	 * @access public
	 */
	function main($content, $conf)	{
		$this->init($conf);
		$this->getTemplateCode(array('SIGN_IN_VIEW', 'THANK_YOU_VIEW'));

		$this->feuser = $GLOBALS['TSFE']->fe_user;
		
		/** Name of the registrationManager class in case someone subclasses it. */
		$registrationManagerClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationmanager');
		$this->registrationManager =& new $registrationManagerClassname();
		
		/** Name of the seminar class in case someone subclasses it. */
		$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
		$this->seminar = new $seminarClassname($this->registrationManager, $this->piVars['seminar']);
		
		$error = $this->registrationManager->canRegisterMessage($this->seminar);
		
		if (empty($error)) {
			$content = $this->pi_getLL('registration_for_event').$this->seminar->getTitleAndDate('&#8211;');
		} else {
			$content = $error;
		}

		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Check whether the logged-in user can register for a seminar.
	 * The validity of the data the user can type in at this page is not checked, though.
	 *
	 * @return	string	empty string if everything is OK, else a localized error message.
	 * @access	private
	 */	
	function canRegister() {
		/** This is empty as long as no error has occured. Used to circumvent deeply-nested ifs. */
		$error = '';
	
		if (!$GLOBALS['TSFE']->loginUser) {
			$error = $this->pi_getLL('please_log_in');
		}

		if (empty($error)) {
			$error = $this->readSeminarFromDB();
		}

		if (empty($error)) {
			if (!$this->seminar[needs_registration]) {
				$error = $this->pi_getLL('no_registration_necessary');
			}
		}

		if (empty($error)) {
			if ($this->seminar[cancelled]) {
				$error = $this->pi_getLL('seminar_cancelled');
			}
		}

		if (empty($error)) {
			if ($this->seminar[is_full]) {
				$error = $this->pi_getLL('seminar_full');
			}
		}

		return $error;
	}
	
	/**
	 * Read the current seminar from the database and writes it as an array to $this->seminar.
	 *
	 * @return	string	empty string if everything is OK, else a localized error message.
	 * @access	private
	 */
	 function readSeminarFromDB() {
	 	$error = '';

		$this->seminar = mysql_fetch_assoc($GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$this->tableSeminars,
			'uid='.$this->seminarUid.t3lib_pageSelect::enableFields($this->tableSeminars),
			'',
			'',
			'1'));
		if (empty($this->seminar)) {
			$error = $this->pi_getLL('wrong_seminar_number');
		}
		
		return $error;
	 }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi3/class.tx_seminars_pi3.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi3/class.tx_seminars_pi3.php']);
}

?>