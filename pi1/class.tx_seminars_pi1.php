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
 * Plugin 'Seminar Manager' for the 'seminars' extension.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationmanager.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('salutationswitcher').'class.tx_salutationswitcher.php');
require_once(t3lib_extMgm::extPath('frontendformslib').'class.tx_frontendformslib.php');

class tx_seminars_pi1 extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_pi1';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_pi1.php';

	/** The seminar for which the user wants to register. */
	var $seminar;
	
	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;
	
	/**
	 * Displays the seminar manager HTML.
	 *
	 * @param	string		Default content string, ignore
	 * @param	array		TypoScript configuration for the plugin
	 * 
	 * @return	string		HTML for the plugin
	 * 
	 * @access public
	 */
	function main($content, $conf) {
		$this->init($conf);
		$this->pi_initPIflexForm();

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();

		// include CSS in header of page
		if ($this->getConfValue('cssFile') !== '') {
			$GLOBALS['TSFE']->additionalHeaderData[] = '<style type="text/css">@import "'.$this->getConfValue('cssFile').'";</style>';
		}

		/** Name of the registrationManager class in case someone subclasses it. */
		$registrationManagerClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationmanager');
		$this->registrationManager =& new $registrationManagerClassname();
		
		$result = '';
		
		switch ($this->getConfValue('what_to_display')) {
			case 'seminar_registration':
				$result = $this->createRegistrationPage(); 
				break;
			case 'my_seminars':
				trigger_error('"My Seminars" is not implemented yet.');
				break;
			case 'seminar_list':
			default:
				$result = $this->createSeminarList();
				break;
		}
		
		return $this->pi_wrapInBaseClass($result);
	}

	/**
	 * Creates the seminar list HTML (either the list view or the single view).
	 *
	 * @return	String		HTML code (shouldn't be empty)
	 *
	 * @access private
	 */
	function createSeminarList() {
		switch ((string) $this->getConfValue('CMD')) {
			case 'singleView':
				list($t) = explode(':', $this->cObj->currentRecord);
				$this->internal['currentTable'] = $t;
				$this->internal['currentRow'] = $this->cObj->data;
				$result = $this->singleView();
				break;
			default:
				// We default to the list view.
				if (strstr($this->cObj->currentRecord, 'tt_content')) {
					$this->conf['pidList'] = $this->getConfValue('pages');
					$this->conf['recursive'] = $this->getConfValue('recursive');
				}
				$result = $this->listView();
				break;
		}
		
		return $result;
	}

	/**
	 * Displays a list of upcoming seminars.
	 *
	 * @return	string		HTML for the plugin
	 * 
	 * @access protected
	 */
	function listView() {
		$result = '';
		
		if ($this->piVars['showUid']) {
			// If a single element should be displayed:
			// XXX Move this code up. CMD seems to be not used.
			$this->internal['currentTable'] = $this->tableSeminars;
			$this->internal['currentRow'] = $this->pi_getRecord($this->tableSeminars, $this->piVars['showUid']);

			$result = $this->singleView();
		} else {
			$this->readSubpartsToHide($this->getConfValue('hideColumns', 's_template_special'), 'LISTHEADER_WRAPPER');
			$this->readSubpartsToHide($this->getConfValue('hideColumns', 's_template_special'), 'LISTITEM_WRAPPER');

			// Local settings for the listView function
			$lConf = $this->conf['listView.'];
	
			if (!isset($this->piVars['pointer'])) {
				$this->piVars['pointer'] = 0;
			}

			// Initializing the query parameters:
			list($this->internal['orderBy'], $this->internal['descFlag']) = explode(':', $this->piVars['sort']);
			// If no sort order is given, sort by beginning date.
			if (empty($this->internal['orderBy'])) {
				$this->internal['orderBy'] = 'begin_date';
			}
			// Number of results to show in a listing.
			$this->internal['results_at_a_time'] = t3lib_div::intInRange($lConf['results_at_a_time'], 0, 1000, 20);
			// The maximum number of 'pages' in the browse-box: 'Page 1', 'Page 2', etc.
			$this->internal['maxPages'] = t3lib_div::intInRange($lConf['maxPages'], 0, 1000, 2);

			$this->internal['searchFieldList'] = 'title,subtitle,description';
			$this->internal['orderByList'] = 'date,begin_date,title,price_regular,organizers';

			/** only show upcoming seminars */
			$inFuture = 'AND end_date >= '.$GLOBALS['SIM_EXEC_TIME'];

			// Get number of records
			$res = $this->pi_exec_query($this->tableSeminars, 1, $inFuture);
			list($this->internal['res_count']) = ($res) ? $GLOBALS['TYPO3_DB']->sql_fetch_row($res) : 0;

			// Make listing query, pass query to SQL database
			$res = $this->pi_exec_query($this->tableSeminars, 0, $inFuture);
			$this->internal['currentTable'] = $this->tableSeminars;

			// Put the whole list together:
			// Adds the whole list table
			$fullTable = $this->pi_list_makelist($res);

			// Adds the search box:
			$fullTable .= $this->pi_list_searchBox();

			// Adds the result browser:
			$fullTable .= $this->pi_list_browseresults();

			// Returns the content from the plugin.
			$result = $fullTable;
		}
		
		return $result;
	}

	/**
	 * Displays detailed data for a seminar.
	 * Fields listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return	string		HTML for the plugin
	 * 
	 * @access protected
	 */
	function singleView() {
		$this->readSubpartsToHide($this->getConfValue('hideFields', 's_template_special'), 'FIELD_WRAPPER');
		
		/** Name of the seminar class in case someone subclasses it. */
		$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
		$currentSeminar =& new $seminarClassname($this->registrationManager, $this->internal['currentRow']['uid']);
		
		
		// This sets the title of the page for use in indexed search results:
		$GLOBALS['TSFE']->indexedDocTitle = $currentSeminar->getTitle();

		$this->setMarkerContent('type', $currentSeminar->getType());
		$this->setMarkerContent('title', $currentSeminar->getTitle());

		if ($currentSeminar->hasSubtitle()) {
			$this->setMarkerContent('subtitle', $currentSeminar->getSubtitle());
		} else {
			$this->readSubpartsToHide('subtitle', 'field_wrapper');
		}

		if ($currentSeminar->hasDescription()) {
			$this->setMarkerContent('description', $currentSeminar->getDescription($this));
		} else {
			$this->readSubpartsToHide('description', 'field_wrapper');
		}

		$this->setMarkerContent('date', $currentSeminar->getDate('&#8211;'));
		$this->setMarkerContent('time', $currentSeminar->getTime('&#8211;'));
		$this->setMarkerContent('place', $currentSeminar->getPlace($this));

		if ($currentSeminar->hasRoom()) {
			$this->setMarkerContent('room', $currentSeminar->getRoom());
		} else {
			$this->readSubpartsToHide('room', 'field_wrapper');
		}

		if ($currentSeminar->hasSpeakers()) {
			$this->setMarkerContent('speakers', $currentSeminar->getSpeakers($this));
		} else {
			$this->readSubpartsToHide('speakers', 'field_wrapper');
		}

		$this->setMarkerContent('price', $currentSeminar->getPrice($this));

		if ($currentSeminar->hasPaymentMethods()) {
			$this->setMarkerContent('paymentmethods', $currentSeminar->getPaymentMethods($this));
		} else {
			$this->readSubpartsToHide('paymentmethods', 'field_wrapper');
		}

		$this->setMarkerContent('organizers', $currentSeminar->getOrganizers($this));

		if ($currentSeminar->needsRegistration()) {
			$this->setMarkerContent('vacancies', $currentSeminar->getVacancies());
		} else {
			$this->readSubpartsToHide('vacancies', 'field_wrapper');
		}

		$this->setMarkerContent('registration', $currentSeminar->getRegistrationLink($this));
		$this->setMarkerContent('backlink', $this->pi_list_linkSingle($this->pi_getLL('label_back', 'Back'), 0));

		return $this->substituteMarkerArrayCached('SINGLE_VIEW');
	}
	
	/**
	 * Returns a list header row as a TR.
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return	string		HTML output, a table row
	 * 
	 * @access protected
	 */
	function pi_list_header() {
		$this->setMarkerContent('header_title',      $this->getFieldHeader_sortLink('title'));
		$this->setMarkerContent('header_date',       $this->getFieldHeader_sortLink('date'));
		$this->setMarkerContent('header_price',      $this->getFieldHeader_sortLink('price_regular'));
		$this->setMarkerContent('header_organizers', $this->getFieldHeader_sortLink('organizers'));
		$this->setMarkerContent('header_vacancies',  $this->getFieldHeader('vacancies'));

		return $this->substituteMarkerArrayCached('LIST_HEADER');
	}

	/**
	 * Returns a list row as a TR. Gets data from $this->internal['currentRow'];
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @param	integer		Row counting. Starts at 0 (zero). Used for alternating class values in the output rows.
	 * 
	 * @return	string		HTML output, a table row with a class attribute set (alternative based on odd/even rows)
	 * 
	 * @access protected
	 */
	function pi_list_row($c) {
		/** Name of the seminar class in case someone subclasses it. */
		$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
		$currentSeminar =& new $seminarClassname($this->registrationManager, $this->internal['currentRow']['uid']);
		
		$this->setMarkerContent('class_itemrow',    ($c % 2) ? 'class="listrow-odd"' : '');
		
		$this->setMarkerContent('title_link',       $currentSeminar->getLinkedTitle($this));
		$this->setMarkerContent('date',             $currentSeminar->getDate('&#8211;'));
		$this->setMarkerContent('price',            $currentSeminar->getPrice());
		$this->setMarkerContent('organizers',       $currentSeminar->getOrganizers($this));
		$this->setMarkerContent('vacancies',        $currentSeminar->needsRegistration() ? $currentSeminar->getVacancies() : '');
		$this->setMarkerContent('class_listvacancies',  $this->getVacanciesClasses($currentSeminar));

		return $this->substituteMarkerArrayCached('LIST_ITEM'); 
	}
	
	/**
	 * Gets the heading for a field type. 
	 *
	 * @param	String		key of the field type for which the heading should be retrieved.
	 * 
	 * @return	String		the heading
	 * 
	 * @access protected
	 */
	function getFieldHeader($fN) {
		$result = '';
	
		switch($fN) {
		case 'title':
			$result = $this->pi_getLL('label_title', '<em>title</em>');
			break;
		default:
			$result = $this->pi_getLL('label_'.$fN, '['.$fN.']');
			break;
		}
		
		return $result;
	}

	/**
	 * Gets the heading for a field type, wrapped in a hyperlink that sorts by that column.
	 *
	 * @param	String		key of the field type for which the heading should be retrieved.
	 * 
	 * @return	String		the heading completely wrapped in a hyperlink
	 * 
	 * @access protected
	 */
	function getFieldHeader_sortLink($fN) {
		$sortField = $fN;
		switch($fN) {
			case 'date':
				$sortField = 'begin_date';
				break;
			default:
				break;
		}
		return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN), array('sort' => $sortField.':'.($this->internal['descFlag'] ? 0 : 1)));
	}

	/**
	 * Gets the CSS classes (space-separated) for the Vacancies TD.
	 *
	 * @param	object		the current Seminar object
	 *
	 * @return	String		a list a space-separated CSS classes (without any quotes and without the class attribute itself)
	 * 
	 * @access protected
	 */
	function getVacanciesClasses(&$seminar) {
		$result = $this->pi_getClassName('vacancies');

		if ($seminar->needsRegistration()) {
			if ($seminar->hasVacancies()) {
				$result .= ' '.$this->pi_getClassName('vacancies-available').' '
					.$this->pi_getClassName('vacancies-'.$seminar->getVacancies());
			} else {
				$result .= ' '.$this->pi_getClassName('vacancies-0');
			}
		}

		return 'class="'.$result.'"';
	}
	
	/**
	 * Creates the HTML for the registration page.
	 * 
	 * @return	String		HTML code for the registration page
	 * 
	 * @acces private
	 */
	function createRegistrationPage() {
		$this->feuser = $GLOBALS['TSFE']->fe_user;
		
		if (!$this->registrationManager->canGenerallyRegister($this->piVars['seminar'])) {
			$errorMessage = $this->registrationManager->canGenerallyRegisterMessage($this->piVars['seminar']);
		} else {
			/** Name of the seminar class in case someone subclasses it. */
			$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
			$this->seminar =& new $seminarClassname($this->registrationManager, $this->piVars['seminar']);
			
			$errorMessage = $this->registrationManager->canUserRegisterForSeminarMessage($this->seminar);
		}

		$result = $this->createRegistrationHeading($errorMessage);
		
		if (empty($errorMessage)) {
			$result .= $this->createRegistrationForm();
		}
		
		return $result;		
	}

	/**
	 * Creates the registration page title and (if applicable) any error messages.
	 * 
	 * @param	String	error message to be displayed (may be empty if there is no error)
	 * 
	 * @return	String	HTML code including the title and error message
	 * 
	 * @access protected
	 */
	function createRegistrationHeading($errorMessage) {
		$this->setMarkerContent('registration', $this->pi_getLL('label_registration'));
		$this->setMarkerContent('title',        ($this->seminar) ? $this->seminar->getTitleAndDate('&#8211;') : '');

		if (empty($errorMessage)) {
			$this->readSubpartsToHide('error', 'wrapper');
		} else {
			$this->setMarkerContent('error_text', $errorMessage);
		}
		
		return $this->substituteMarkerArrayCached('REGISTRATION_HEAD');
	}
	 
	/**
	 * Creates the registration form.
	 * 
	 * @return	String		HTML code for the form
	 * 
	 * @access protected
	 */
	function createRegistrationForm() {
		// Create the frontend form object:
		$className = t3lib_div::makeInstanceClassName('tx_frontendformslib');
		$formObj = new $className($this);
		
		// Generate configuration for a single step displaying certain fields of tt_address:
		$formObj->steps[1] = $formObj->createStepConf($this->getConfValue('showRegistrationFields'), $this->tableAttendances, $this->pi_getLL('label_registrationForm'), '<p>'.$this->pi_getLL('message_registrationForm').'</p>');
		
		$formObj->init();
		
		// Check if the form has been submitted:
		if ($formObj->submitType == 'submit') {
			$this->registrationManager->createRegistration($this->seminar, $formObj->sessionData['data'][$this->tableAttendances]);
			
			$output = $this->substituteMarkerArrayCached('REGISTRATION_THANKYOU');
			// Destroy session data for our submitted form:
			$formObj->destroySessionData();
		} else {
			$this->setMarkerContent('price', $this->seminar->getPrice());
			$this->setMarkerContent('vacancies', $this->seminar->getVacancies());
			$output = $this->substituteMarkerArrayCached('REGISTRATION_DETAILS');
			// Form has not yet been submitted, so render the form:
			$output .= $formObj->renderWholeForm();
		}
		
		return $output;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']);
}

?>