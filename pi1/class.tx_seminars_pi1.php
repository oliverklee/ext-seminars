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

class tx_seminars_pi1 extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_pi1';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_pi1.php';

	/** Cache the organizers data for the list view */
	var $organizersCache = array();
	
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

		$result = '';
		
		switch ($this->getConfValue('what_to_display')) {
			case 'seminar_registration':
				$this->feuser = $GLOBALS['TSFE']->fe_user;
				
				/** Name of the registrationManager class in case someone subclasses it. */
				$registrationManagerClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationmanager');
				$this->registrationManager =& new $registrationManagerClassname();
				
				/** Name of the seminar class in case someone subclasses it. */
				$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
				$this->seminar =& new $seminarClassname($this->registrationManager, $this->piVars['seminar']);
				
				$errorMessage = $this->registrationManager->canRegisterMessage($this->seminar);
				$result = $this->createHeading($errorMessage);
				break;
			case 'my_seminars':
				trigger_error('"My Seminars" is not implemented yet.');
				break;
			case 'seminar_list':
			default:
				switch ((string) $conf['CMD']) {
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
				break;
		}
		
		return $this->pi_wrapInBaseClass($result);
	}

	/**
	 * Displays a list of upcoming seminars.
	 *
	 * @return	string		HTML for the plugin
	 * 
	 * @access protected
	 */
	function listView() {
		$this->readSubpartsToHide($this->getConfValue('hideColumns', 's_template_special'), 'LISTHEADER_WRAPPER');
		$this->readSubpartsToHide($this->getConfValue('hideColumns', 's_template_special'), 'LISTITEM_WRAPPER');

		// Local settings for the listView function
		$lConf = $this->conf['listView.'];

		if ($this->piVars['showUid']) {
			// If a single element should be displayed:
			// XXX Do we need this code?
			$this->internal['currentTable'] = 'tx_seminars_seminars';
			$this->internal['currentRow'] = $this->pi_getRecord('tx_seminars_seminars', $this->piVars['showUid']);

			return $this->singleView();
		} else {
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
			$res = $this->pi_exec_query('tx_seminars_seminars', 1, $inFuture);
			list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

			// Make listing query, pass query to SQL database
			$res = $this->pi_exec_query('tx_seminars_seminars', 0, $inFuture);
			$this->internal['currentTable'] = 'tx_seminars_seminars';

			// Put the whole list together:
			// Adds the whole list table
			$fullTable = $this->pi_list_makelist($res);

			// Adds the search box:
			$fullTable .= $this->pi_list_searchBox();

			// Adds the result browser:
			$fullTable .= $this->pi_list_browseresults();

			// Returns the content from the plugin.
			return $fullTable;
		}
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
		
		// This sets the title of the page for use in indexed search results:
		if ($this->internal['currentRow']['title']) {
			$GLOBALS['TSFE']->indexedDocTitle = $this->internal['currentRow']['title'];
		}

		$this->setMarkerContent('TYPE', $this->getConfValue('eventType'));
		$this->setMarkerContent('TITLE', $this->getFieldContent('title'));

		$this->setMarkerContent('SUBTITLE', $this->getFieldContent('subtitle'));
		if (empty($this->markers['###SUBTITLE###'])) {
			$this->readSubpartsToHide('subtitle', 'field');
		}

		$this->setMarkerContent('DESCRIPTION', $this->getFieldContent('description'));
		if (empty($this->markers['###DESCRIPTION###'])) {
			$this->readSubpartsToHide('description', 'field');
		}

		$this->setMarkerContent('DATE', $this->getFieldContent('date'));
		$this->setMarkerContent('TIME', $this->getFieldContent('time'));
		$this->setMarkerContent('PLACE', $this->pi_RTEcssText($this->getFieldContent('place')));

		$this->setMarkerContent('ROOM', $this->getFieldContent('room'));
		if (empty($this->markers['###ROOM###'])) {
			$this->readSubpartsToHide('room', 'field');
		}

		$this->setMarkerContent('SPEAKERS', $this->getFieldContent('speakers'));
		if (!empty($this->markers['###SPEAKERS###'])) {
			$this->setMarkerContent('SPEAKERS', $this->pi_RTEcssText($this->markers['###SPEAKERS###']));
		} else {
			$this->readSubpartsToHide('speakers', 'field');
		}

		$this->setMarkerContent('PRICE', $this->getFieldContent('price_regular'));
		$this->setMarkerContent('ORGANIZERS', $this->getFieldContent('organizers'));

		if ($this->internal['currentRow']['needs_registration']) {
			$this->setMarkerContent('VACANCIES', $this->getFieldContent('vacancies'));
		} else {
			$this->readSubpartsToHide('vacancies', 'field');
		}

		$this->setMarkerContent('REGISTRATION', $this->getFieldContent('registration'));

		$this->setMarkerContent('BACKLINK', $this->pi_list_linkSingle($this->pi_getLL('label_back', 'Back'), 0));

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
		$this->setMarkerContent('HEADER_TITLE',      $this->getFieldHeader_sortLink('title'));
		$this->setMarkerContent('HEADER_DATE',       $this->getFieldHeader_sortLink('date'));
		$this->setMarkerContent('HEADER_PRICE',      $this->getFieldHeader_sortLink('price_regular'));
		$this->setMarkerContent('HEADER_ORGANIZERS', $this->getFieldHeader_sortLink('organizers'));
		$this->setMarkerContent('HEADER_VACANCIES',  $this->getFieldHeader('vacancies'));

		return $this->substituteMarkerArrayCached('LIST_HEADER');
	}

	/**
	 * Returns a list row as a TR. Get data from $this->internal['currentRow'];
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @param	integer		Row counting. Starts at 0 (zero). Used for alternating class values in the output rows.
	 * 
	 * @return	string		HTML output, a table row with a class attribute set (alternative based on odd/even rows)
	 * 
	 * @access protected
	 */
	function pi_list_row($c) {
		$this->setMarkerContent('CLASS_ITEMROW',    ($c % 2) ? 'class="listrow-odd"' : '');
		
		$this->setMarkerContent('TITLE_LINK',       $this->getFieldContent('title'));
		$this->setMarkerContent('DATE',             $this->getFieldContent('date'));
		$this->setMarkerContent('PRICE',            $this->getFieldContent('price_regular'));
		$this->setMarkerContent('ORGANIZERS',       $this->getFieldContent('organizers'));
		$this->setMarkerContent('VACANCIES',        $this->getFieldContent('vacancies'));
		$this->setMarkerContent('CLASS_LISTVACANCIES',  $this->getVacanciesClasses());

		return $this->substituteMarkerArrayCached('LIST_ITEM'); 
	}
	
	/**
	 * Gets the content for a field (e.g. seminar description).
	 *
	 * @param	String		key of the field for which the content should be retrieved
	 * 
	 * @return	String		the field content (may be empty)
	 * 
	 * @access protected
	 */
	function getFieldContent($fN) {
		switch($fN) {
			case 'title':
				// This will wrap the title in a link.
				// The '1' means that the display of single items is CACHED! Set to zero to disable caching.
				return $this->pi_list_linkSingle($this->internal['currentRow']['title'], $this->internal['currentRow']['uid'], 0);
			break;
			case 'description':
				return $this->pi_RTEcssText($this->internal['currentRow']['description']);
			break;
			case 'date':
				$beginDate = $this->internal['currentRow']['begin_date'];
				$beginDateDay = strftime('%d.%m.%Y', $beginDate);
				$endDate = $this->internal['currentRow']['end_date'];
				$endDateDay = strftime('%d.%m.%Y', $endDate);

				if (!empty($beginDate)) {
					// Does the workshop span several days?
					if (!empty($endDate) && $beginDateDay !== $endDateDay) {
						$result = '&#8211;'.$endDateDay;
						// Are the years different? Then include the complete begin date.
						if (strftime('%Y', $beginDate) !== strftime('%Y', $endDate)) {
							$result = $beginDateDay.$result;
						} else {
							// Are the months different? Then include day and month.
							if (strftime('%m', $beginDate) !== strftime('%m', $endDate)) {
								$result = strftime('%d.%m.', $beginDate).$result;
							} else {
								$result = strftime('%d.', $beginDate).$result;
							}
						}

					} else {
						$result = $beginDateDay;
					}
				} else {
					$result = '<em>'.$this->pi_getLL('willBeAnnounced').'</em>';
				}
				return $result;
			break;
				case 'time':
				$beginDate = $this->internal['currentRow']['begin_date'];
				$beginDateTime = strftime('%H:%M', $beginDate);
				$endDate = $this->internal['currentRow']['end_date'];
				$endDateTime = strftime('%H:%M', $endDate);

				if (!empty($beginDate)) {
					$result = $beginDateTime.'&#8211;'.$endDateTime;
				} else {
					$result = '<em>'.$this->pi_getLL('message_willBeAnnounced').'</em>';
				}
				return $result;
			break;
			case 'begin_date':
				$date = $this->internal['currentRow']['begin_date'];
				return (!empty($date)) ? strftime('%d-%m-%Y %H:%M', $date) :
					'&nbsp;';
			break;
			case 'end_date':
				$date = $this->internal['currentRow']['end_date'];
				return (!empty($date)) ? strftime('%d-%m-%Y %H:%M', $date) :
					'&nbsp;';
			break;
			case 'price_regular':
				return $this->internal['currentRow']['price_regular'].'&nbsp;EUR';
			break;
			case 'organizers':
				if (!count($this->organizersCache)) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_seminars_organizers', '', '', '', '');
					while ($row = mysql_fetch_assoc($res)) {
						$this->organizersCache[$row['uid']] = $row;
					}
				}
				$result = '';
				$organizers = explode(',', $this->internal['currentRow']['organizers']);
				foreach($organizers as $currentOrganizer) {
					if (!empty($result)) {
						$result .= ', ';
					}
					$result .= '<a href="'.$this->organizersCache[$currentOrganizer]['homepage'].'">'. htmlspecialchars($this->organizersCache[$currentOrganizer]['title']).'</a>';
				}
				return $result;
			break;
			case 'speakers':
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'title, organization, homepage, description',
					'tx_seminars_speakers, tx_seminars_seminars_speakers_mm',
					'uid_local='.$this->internal['currentRow']['uid'].' AND uid=uid_foreign' .$this->cObj->enableFields('tx_seminars_speakers'),
					'',
					'',
					'' );

				$result = '';
				while ($row = mysql_fetch_assoc($res)) {
					$name = htmlspecialchars($row['title']);
					if (!empty($row['organization'])) {
						$name .= ', '.htmlspecialchars($row['organization']);
					}
					if (!empty($row['homepage'])) {
						$name = '<a href="'.$row['homepage'].'">'.$name.'</a>';
					}
					$result .= $name.chr(10);
					if (!empty($row['description'])) {
					$result .= $this->pi_RTEcssText($row['description']);
					}
				}
				return $result;
			break;
			case 'place':
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title, address, homepage, directions',
					'tx_seminars_sites, tx_seminars_seminars_place_mm',
					'uid_local='.$this->internal['currentRow']['uid'].' AND uid=uid_foreign',
					'',
					'',
					'');

				$result = '';
				while ($row = mysql_fetch_assoc($res)) {
					$name = htmlspecialchars($row['title']);
					if (!empty($row['homepage'])) {
						$name = '<a href="'.$row['homepage'].'">'.$name.'</a>';
					}
					$result .= $name;
					if (!empty($row['address'])) {
						$result .= '<br />'.$row['address'];
					}
					if (!empty($row['directions'])) {
						$result .= $this->pi_RTEcssText($row['address']);
					}
				}
				return $result;
			break;
			case 'registration':
				$result = '';
				if ($this->internal['currentRow']['needs_registration']) {
					if ($this->internal['currentRow']['is_full']) {
						$result = $this->pi_getLL('message_noVacancies');
					} else {
						$organizers = explode(',', $this->internal['currentRow']['organizers']);
						foreach($organizers as $currentOrganizer) {
							$result = '<a href="'.$this->organizersCache[$currentOrganizer]['registration_page']. 										'&subject=Anmeldung+zum+Seminar+'.urlencode($this->internal['currentRow']['title']).' '.urlencode($this->getFieldContent('begin_date')).'">'. $this->pi_getLL('label_onlineRegistration').'</a>';
						}
					}
				} else {
					$result = $this->pi_getLL('message_noRegistrationNecessary');
				}
				return $result;
			break;
			case 'vacancies':
				if ($this->internal['currentRow']['needs_registration']) {
					if ($this->internal['currentRow']['is_full']) {
						$result = 0;
					} else {
						$result = $this->internal['currentRow']['attendees_max'] - $this->internal['currentRow']['attendees'];
					}
				} else {
					$result = '&nbsp;';
				}
				return $result;
			break;
			default:
				return $this->internal['currentRow'][$fN];
			break;
		}
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
		return $this->pi_getLL('label_'.$fN, '['.$fN.']');
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
		return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN), array('sort' => $sortField.':'.($this->internal['descFlag']?0:1)));
	}

	/**
	 * Gets the CSS classes (space-separated) for the Vacancies TD.
	 *
	 * @return	String		a list a space-separated CSS classes (without any quotes and without the class attribute itself)
	 * 
	 * @access protected
	 */
	function getVacanciesClasses() {
		$result = $this->pi_getClassName('vacancies');

		if ($this->internal['currentRow']['needs_registration']) {
			if ($this->internal['currentRow']['is_full']) {
				$result .= ' '.$this->pi_getClassName('vacancies-0');
			} else {
				$result .= ' '.$this->pi_getClassName('vacancies-available').' '.$this->pi_getClassName('vacancies-')
					.($this->internal['currentRow']['attendees_max'] - $this->internal['currentRow']['attendees']);
			}
		}

		return 'class="'.$result.'"';
	}
	
	/**
	 * Checks whether the logged-in user can register for a seminar.
	 * The validity of the data the user can type in at this page is not checked, though.
	 *
	 * @return	string	empty string if everything is OK, else a localized error message.
	 * 
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
			if (!$this->seminar['needs_registration']) {
				$error = $this->pi_getLL('message_noRegistrationNecessary');
			}
		}

		if (empty($error)) {
			if ($this->seminar['cancelled']) {
				$error = $this->pi_getLL('message_seminarCancelled');
			}
		}

		if (empty($error)) {
			if ($this->seminar['is_full']) {
				$error = $this->pi_getLL('message_noVacancies');
			}
		}

		return $error;
	}
	
	/**
	 * Reads the current seminar from the database and writes it as an array to $this->seminar.
	 *
	 * @return	string	empty string if everything is OK, else a localized error message.
	 * 
	 * @access	protected
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
			$error = $this->pi_getLL('message_wrongSeminarNumber');
		}
		
		return $error;
	 }
	 
	 /**
	  * Creates the page title and (if applicable) any error messages.
	  * 
	  * When this method is called, the function getTemplateCode() must already have been called.
	  * 
	  * @param	string	error message to be displayed (may be empty if there is no error)
	  * 
	  * @return	string	HTML code including the title and error message
	  * 
	  * @access protected
	  */
	 function createHeading($errorMessage) {
		$this->setMarkerContent('REGISTRATION', $this->pi_getLL('registration'));
		$this->setMarkerContent('TITLE',        $this->seminar->getTitleAndDate('&#8211;'));

		if (empty($errorMessage)) {
			$this->readSubpartsToHide('error', 'wrapper');
		} else {
			$this->setMarkerContent('ERROR_TEXT', $errorMessage);
		}
		
		return $this->substituteMarkerArrayCached('REGISTRATION_HEAD');
	 }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']);
}

?>