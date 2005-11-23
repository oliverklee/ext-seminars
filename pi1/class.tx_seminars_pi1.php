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
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationmanager.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('frontendformslib').'class.tx_frontendformslib.php');

class tx_seminars_pi1 extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_pi1';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_pi1.php';

	/** The seminar which we want to list/show or for which the user wants to register. */
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
	 * @access	public
	 */
	function main($content, $conf) {
		$this->init($conf);
		$this->pi_initPIflexForm();

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();

		// include CSS in header of page
		if ($this->getConfValue('cssFile', 's_template_special') !== '') {
			$GLOBALS['TSFE']->additionalHeaderData[] = '<style type="text/css">@import "'.$this->getConfValue('cssFile', 's_template_special').'";</style>';
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
	 * @return	string		HTML code (shouldn't be empty)
	 *
	 * @access	private
	 */
	function createSeminarList() {
		// if we have a 'showUid' var set, we'll show the detailed view
		if ($this->piVars['showUid']) {
			$this->internal['currentTable'] = $this->tableSeminars;
			$this->internal['currentRow'] = $this->pi_getRecord($this->tableSeminars, $this->piVars['showUid']);
			$result = $this->singleView();
		} else {
			if (strstr($this->cObj->currentRecord, 'tt_content')) {
				$this->conf['pidList'] = $this->getConfValue('pages');
				$this->conf['recursive'] = $this->getConfValue('recursive');
			}
			$result = $this->listView();
		}

		return $result;
	}

	/**
	 * Displays a list of upcoming seminars.
	 *
	 * @return	string		HTML for the plugin
	 *
	 * @access	protected
	 */
	function listView() {
		$result = '';
		$this->readSubpartsToHide($this->getConfValue('hideColumns', 's_template_special'), 'LISTHEADER_WRAPPER');
		$this->readSubpartsToHide($this->getConfValue('hideColumns', 's_template_special'), 'LISTITEM_WRAPPER');

		// hide the registration column if no user is logged in
		if (!$this->registrationManager->isLoggedIn()) {
			$this->readSubpartsToHide('registration', 'LISTHEADER_WRAPPER');
			$this->readSubpartsToHide('registration', 'LISTITEM_WRAPPER');
		}

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

		$this->internal['searchFieldList'] = 'title,subtitle,description,accreditation_number';
		$this->internal['orderByList'] = 'title,accreditation_number,credit_points,begin_date,price_regular,price_special,organizers';

		/** only show upcoming seminars */
		$inFuture = 'AND end_date >= '.$GLOBALS['SIM_EXEC_TIME'];

		// Get number of records
		$res = $this->pi_exec_query($this->tableSeminars, 1, $inFuture);
		list($this->internal['res_count']) = ($res) ? $GLOBALS['TYPO3_DB']->sql_fetch_row($res) : 0;

		if ($this->internal['res_count']) {
			// Make listing query, pass query to SQL database
			$res = $this->pi_exec_query($this->tableSeminars, 0, $inFuture);
			$this->internal['currentTable'] = $this->tableSeminars;

			// Put the whole list together:
			// Adds the whole list table
			$fullTable = $this->pi_list_makelist($res);
		} else {
			$this->setMarkerContent('error_text', $this->pi_getLL('message_noResults'));
			$fullTable = $this->substituteMarkerArrayCached('ERROR_VIEW');
		}

		// Adds the search box:
		$fullTable .= $this->pi_list_searchBox();

		// Adds the result browser:
		$fullTable .= $this->pi_list_browseresults();

		// Returns the content from the plugin.
		$result = $fullTable;

		return $result;
	}

	/**
	 * Displays detailed data for a seminar.
	 * Fields listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return	string		HTML for the plugin
	 *
	 * @access	protected
	 */
	function singleView() {
		$this->readSubpartsToHide($this->getConfValue('hideFields', 's_template_special'), 'FIELD_WRAPPER');

		if ($this->createSeminar($this->internal['currentRow']['uid'])) {
			// This sets the title of the page for use in indexed search results:
			$GLOBALS['TSFE']->indexedDocTitle = $this->seminar->getTitle();

			$this->setMarkerContent('type', $this->seminar->getType());
			$this->setMarkerContent('title', $this->seminar->getTitle());

			if ($this->seminar->hasSubtitle()) {
				$this->setMarkerContent('subtitle', $this->seminar->getSubtitle());
			} else {
				$this->readSubpartsToHide('subtitle', 'field_wrapper');
			}

			if ($this->seminar->hasDescription()) {
				$this->setMarkerContent('description', $this->seminar->getDescription($this));
			} else {
				$this->readSubpartsToHide('description', 'field_wrapper');
			}

			if ($this->seminar->hasAccreditationNumber()) {
				$this->setMarkerContent('accreditation_number', $this->seminar->getAccreditationNumber());
			} else {
				$this->readSubpartsToHide('accreditation_number', 'field_wrapper');
			}

			if ($this->seminar->hasCreditPoints()) {
				$this->setMarkerContent('credit_points', $this->seminar->getCreditPoints());
			} else {
				$this->readSubpartsToHide('credit_points', 'field_wrapper');
			}

			$this->setMarkerContent('date', $this->seminar->getDate());
			$this->setMarkerContent('time', $this->seminar->getTime());
			$this->setMarkerContent('place', $this->seminar->getPlace($this));

			if ($this->seminar->hasRoom()) {
				$this->setMarkerContent('room', $this->seminar->getRoom());
			} else {
				$this->readSubpartsToHide('room', 'field_wrapper');
			}

			if ($this->seminar->hasSpeakers()) {
				$this->setMarkerContent('speakers', $this->seminar->getSpeakers($this));
			} else {
				$this->readSubpartsToHide('speakers', 'field_wrapper');
			}

			if ($this->getConfValue('generalPriceInSingle', 's_template_special')) {
				$this->setMarkerContent('label_price_regular', $this->pi_getLL('label_price_general'));
			}
			$this->setMarkerContent('price_regular', $this->seminar->getPriceRegular());

			if ($this->seminar->hasPriceSpecial()) {
				$this->setMarkerContent('price_special', $this->seminar->getPriceSpecial());
			} else {
				$this->readSubpartsToHide('price_special', 'field_wrapper');
			}

			if ($this->seminar->hasPaymentMethods()) {
				$this->setMarkerContent('paymentmethods', $this->seminar->getPaymentMethods($this));
			} else {
				$this->readSubpartsToHide('paymentmethods', 'field_wrapper');
			}

			$this->setMarkerContent('organizers', $this->seminar->getOrganizers($this));

			if ($this->seminar->needsRegistration()) {
				$this->setMarkerContent('vacancies', $this->seminar->getVacancies());
			} else {
				$this->readSubpartsToHide('vacancies', 'field_wrapper');
			}

			if ($this->seminar->hasRegistrationDeadline()) {
				$this->setMarkerContent('deadline_registration', $this->seminar->getRegistrationDeadline());
			} else {
				$this->readSubpartsToHide('deadline_registration', 'field_wrapper');
			}

			$this->setMarkerContent('registration',
				$this->registrationManager->canRegisterIfLoggedIn($this->seminar) ?
					$this->registrationManager->getLinkToRegistrationOrLoginPage($this, $this->seminar) :
					$this->registrationManager->canRegisterIfLoggedInMessage($this->seminar)
			);
			$this->setMarkerContent('backlink', $this->pi_list_linkSingle($this->pi_getLL('label_back', 'Back'), 0));

			$result = $this->substituteMarkerArrayCached('SINGLE_VIEW');
		} else {
			$this->setMarkerContent('error_text', $this->pi_getLL('message_wrongSeminarNumber'));
			$result = $this->substituteMarkerArrayCached('ERROR_VIEW');
			header('Status: 404 Not Found');
		}

		return $result;
	}

	/**
	 * Creates a seminar in $this->seminar.
	 * $this->registrationManager must have been initialized before this method may be called.
	 *
	 * @param	int			a seminar UID
	 *
	 * @return	boolean		true if the seminar UID is valid and the object has been created, false otherwise
	 *
	 * @access	private
	 */
	function createSeminar($seminarUid) {
		$result = false;

		if (tx_seminars_seminar::existsSeminar($seminarUid)) {
			/** Name of the seminar class in case someone subclasses it. */
			$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
			$this->seminar =& new $seminarClassname($this->registrationManager, $seminarUid);
			$result = true;
		}

		return $result;
	}

	/**
	 * Returns a list header row as a TR.
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return	string		HTML output, a table row
	 *
	 * @access	protected
	 */
	function pi_list_header() {
		$this->setMarkerContent('header_title', $this->getFieldHeader_sortLink('title'));
		$this->setMarkerContent('header_accreditation_number', $this->getFieldHeader_sortLink('accreditation_number'));
		$this->setMarkerContent('header_credit_points', $this->getFieldHeader_sortLink('credit_points'));
		$this->setMarkerContent('header_date', $this->getFieldHeader_sortLink('date'));
		$this->setMarkerContent('header_price_regular', $this->getFieldHeader_sortLink('price_regular'));
		$this->setMarkerContent('header_price_special', $this->getFieldHeader_sortLink('price_special'));
		$this->setMarkerContent('header_organizers', $this->getFieldHeader_sortLink('organizers'));
		$this->setMarkerContent('header_vacancies', $this->getFieldHeader('vacancies'));
		$this->setMarkerContent('header_registration', $this->getFieldHeader('registration'));

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
	 * @access	protected
	 */
	function pi_list_row($c) {
		if ($this->createSeminar($this->internal['currentRow']['uid'])) {
			$rowClass = ($c % 2) ? 'listrow-odd' : '';
			$canceledClass = ($this->seminar->isCanceled()) ? $this->pi_getClassName('cancelled') : '';
			// If we have two classes, we need a space as a separator.
			$classSeparator = (!empty($rowClass) && !empty($canceledClass)) ? ' ' : '';
			// Only use the class construct if we actually have a class.
			$completeClass = (!empty($rowClass) || !empty($canceledClass)) ?
				' class="'.$rowClass.$classSeparator.$canceledClass.'"' :
				'';

			$this->setMarkerContent('class_itemrow', $completeClass);

			$this->setMarkerContent('title_link', $this->seminar->getLinkedTitle($this));
			$this->setMarkerContent('accreditation_number', $this->seminar->getAccreditationNumber());
			$this->setMarkerContent('credit_points', $this->seminar->getCreditPoints());
			$this->setMarkerContent('date', $this->seminar->getDate());
			$this->setMarkerContent('price_regular', $this->seminar->getPriceRegular());
			$this->setMarkerContent('price_special', $this->seminar->getPriceSpecial());
			$this->setMarkerContent('organizers', $this->seminar->getOrganizers($this));
			$this->setMarkerContent('vacancies', $this->seminar->needsRegistration() ? $this->seminar->getVacancies() : '');
			$this->setMarkerContent('class_listvacancies', $this->getVacanciesClasses($this->seminar));
			$this->setMarkerContent('registration', $this->registrationManager->canRegisterIfLoggedIn($this->seminar) ?
				$this->registrationManager->getLinkToRegistrationOrLoginPage($this, $this->seminar) : ''
			);
		}

		return $this->substituteMarkerArrayCached('LIST_ITEM');
	}

	/**
	 * Gets the heading for a field type.
	 *
	 * @param	string		key of the field type for which the heading should be retrieved.
	 *
	 * @return	string		the heading
	 *
	 * @access	protected
	 */
	function getFieldHeader($fN) {
		$result = '';

		switch($fN) {
		case 'title':
			$result = $this->pi_getLL('label_title', '<em>title</em>');
			break;
		case 'price_regular':
			if ($this->getConfValue('generalPriceInList', 's_template_special')) {
				$fN = 'price_general';
			}
			// fall-through is intended here
		default:
			$result = $this->pi_getLL('label_'.$fN, '['.$fN.']');
			break;
		}

		return $result;
	}

	/**
	 * Gets the heading for a field type, wrapped in a hyperlink that sorts by that column.
	 *
	 * @param	string		key of the field type for which the heading should be retrieved.
	 *
	 * @return	string		the heading completely wrapped in a hyperlink
	 *
	 * @access	protected
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
	 * @return	string		class attribute filled with a list a space-separated CSS classes, plus a leading space
	 *
	 * @access	protected
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

		return ' class="'.$result.'"';
	}

	/**
	 * Creates the HTML for the registration page.
	 *
	 * @return	string		HTML code for the registration page
	 *
	 * @acces	private
	 */
	function createRegistrationPage() {
		$this->feuser = $GLOBALS['TSFE']->fe_user;

		if ($this->createSeminar($this->piVars['seminar'])) {
			if (!$this->registrationManager->canRegisterIfLoggedIn($this->seminar)) {
				$errorMessage = $this->registrationManager->canRegisterIfLoggedInMessage($this->seminar);
			} else {
				if (!$this->registrationManager->isLoggedIn()) {
					$errorMessage = $this->registrationManager->getLinkToRegistrationOrLoginPage($this, $this->seminar);
				}
			}
		} else {
			$errorMessage = $this->registrationManager->existsSeminarMessage($this->piVars['seminar'], $this);
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
	 * @param	string	error message to be displayed (may be empty if there is no error)
	 *
	 * @return	string	HTML code including the title and error message
	 *
	 * @access	protected
	 */
	function createRegistrationHeading($errorMessage) {
		$this->setMarkerContent('registration', $this->pi_getLL('label_registration'));
		$this->setMarkerContent('title',        ($this->seminar) ? $this->seminar->getTitleAndDate() : '');

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
	 * @return	string		HTML code for the form
	 *
	 * @access	protected
	 */
	function createRegistrationForm() {
		// Create the frontend form object:
		$className = t3lib_div::makeInstanceClassName('tx_frontendformslib');
		$formObj = new $className($this);

		// Generate configuration for a single step displaying certain fields of tt_address:
		$formObj->steps[1] = $formObj->createStepConf($this->getConfValue('showRegistrationFields', 's_template_special'), $this->tableAttendances, $this->pi_getLL('label_registrationForm'), '<p>'.$this->pi_getLL('message_registrationForm').'</p>');
		$formObj->init();

		// Check if the form has been submitted:
		if ($formObj->submitType == 'submit') {
			$this->registrationManager->createRegistration($this->seminar, $formObj->sessionData['data'][$this->tableAttendances], $this);

			$output = $this->substituteMarkerArrayCached('REGISTRATION_THANKYOU');
			// Destroy session data for our submitted form:
			$formObj->destroySessionData();
		} else {
			if ($this->getConfValue('generalPriceInSingle', 's_template_special')) {
				$this->setMarkerContent('label_price_regular', $this->pi_getLL('label_price_general'));
			}
			$this->setMarkerContent('price_regular', $this->seminar->getPriceRegular());
			if ($this->seminar->hasPriceSpecial()) {
				$this->setMarkerContent('price_special', $this->seminar->getPriceSpecial());
			} else {
				$this->readSubpartsToHide('price_special', 'registration_wrapper');
			}
			$this->setMarkerContent('vacancies', $this->seminar->getVacancies());
			$output = $this->substituteMarkerArrayCached('REGISTRATION_DETAILS');
			// Form has not yet been submitted, so render the form:
			$output .= $formObj->renderWholeForm();
			$output .= $this->substituteMarkerArrayCached('REGISTRATION_BOTTOM');
		}

		return $output;
	}

	/**
	 * Returns the list of items based on the input SQL result pointer.
	 * For each result row the internal var, $this->internal['currentRow'], is set with the row returned.
	 *
	 * $this->pi_list_header() makes the header row for the list
	 * $this->pi_list_row() is used for rendering each row
	 *
	 * @param	pointer		Result pointer to a SQL result which can be traversed.
	 * @param	string		Attributes for the table tag which is wrapped around the table rows containing the list
	 * @return	string		Output HTML, wrapped in <div>-tags with a class attribute
	 *
	 * @access	protected
	 *
	 * @see pi_list_row(), pi_list_header()
	 */
	function pi_list_makelist($res, $tableParams = '')	{
		// Make list table header
		$tRows = array();
		$this->internal['currentRow'] = '';
		$tRows[] = $this->pi_list_header();
		$tRows[] = '  <tbody>'.chr(10);

		// Make list table rows
		$c = 0;
		while ($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tRows[] = $this->pi_list_row($c);
			$c++;
		}
		$tRows[] = '  </tbody>'.chr(10);

		$output = '<div'.$this->pi_classParam('listrow').'>'.chr(10);
		$output .= '<'.trim('table '.$tableParams).'>'.implode('',$tRows).'</table>'.chr(10);
		$output .= '</div>';

		return $output;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']);
}

?>