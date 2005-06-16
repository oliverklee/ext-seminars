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

class tx_seminars_pi1 extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_pi1';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_pi1.php';

	/** Cache the organizers data for the list view */
	var $organizersCache = array();

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
		$this->getTemplateCode(array('LIST_HEADER', 'LIST_ITEM', 'SINGLE_VIEW'));
		
		switch ((string) $conf['CMD']) {
			case 'singleView':
				list($t) = explode(':', $this->cObj->currentRecord);
				$this->internal['currentTable'] = $t;
				$this->internal['currentRow'] = $this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView());
			break;
			default:
				// We default to the list view.
				if (strstr($this->cObj->currentRecord, 'tt_content')) {
					$this->conf['pidList'] = $this->cObj->data['pages'];
					$this->conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->listView());
			break;
		}
	}

	/**
	 * Displays a list of upcoming seminars.
	 *
	 * @return	string		HTML for the plugin
	 * 
	 * @access protected
	 */
	function listView() {
		$this->readSubpartsToHide('COLUMN', $this->conf['hideColumns']);

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
	 *
	 * @return	string		HTML for the plugin
	 * 
	 * @access protected
	 */
	function singleView() {
		$this->readSubpartsToHide('FIELD', $this->conf['hideFields']);
		
		// This sets the title of the page for use in indexed search results:
		if ($this->internal['currentRow']['title']) {
			$GLOBALS['TSFE']->indexedDocTitle = $this->internal['currentRow']['title'];
		}

		$markers = array();
		$markers['###TYPE###'] = 'Workshop';
		$markers['###TITLE###'] = $this->getFieldContent('title');

		$markers['###SUBTITLE###'] = $this->getFieldContent('subtitle');
		if (empty($markers['###SUBTITLE###'])) {
			$this->subpartsToHide['###FIELD_SUBTITLE###'] = '';
		}

		$markers['###DESCRIPTION###'] = $this->getFieldContent('description');
		if (empty($markers['###DESCRIPTION###'])) {
			$this->subpartsToHide['###FIELD_DESCRIPTION###'] = '';
		}

		$markers['###HEADER_DATE###'] = $this->getFieldHeader('date');
		$markers['###DATE###'] = $this->getFieldContent('date');

		$markers['###HEADER_TIME###'] = $this->getFieldHeader('time');
		$markers['###TIME###'] = $this->getFieldContent('time');

		$markers['###HEADER_PLACE###'] = $this->getFieldHeader('place');
		$markers['###PLACE###'] = $this->pi_RTEcssText($this->getFieldContent('place'));

		$markers['###HEADER_ROOM###'] = $this->getFieldHeader('room');
		$markers['###ROOM###'] = $this->getFieldContent('room');
		if (empty($markers['###ROOM###'])) {
			$this->subpartsToHide['###FIELD_ROOM###'] = '';
		}

		$markers['###HEADER_SPEAKERS###'] = $this->getFieldHeader('speakers');
		$markers['###SPEAKERS###'] = $this->getFieldContent('speakers');
		if (!empty($markers['###SPEAKERS###'])) {
			$markers['###SPEAKERS###'] = $this->pi_RTEcssText($markers['###SPEAKERS###']);
		} else {
			$this->subpartsToHide['###FIELD_SPEAKERS###'] = '';
		}

		$markers['###HEADER_PRICE###'] = $this->getFieldHeader('price_regular');
		$markers['###PRICE###'] = $this->getFieldContent('price_regular');

		$markers['###HEADER_ORGANIZERS###'] = $this->getFieldHeader('organizers');
		$markers['###ORGANIZERS###'] = $this->getFieldContent('organizers');

		if ($this->internal['currentRow']['needs_registration']) {
			$markers['###HEADER_VACANCIES###'] = $this->getFieldHeader('vacancies');
			$markers['###VACANCIES###'] = $this->getFieldContent('vacancies');
		} else {
			$this->subpartsToHide['###FIELD_VACANCIES###'] = '';
		}

		$markers['###HEADER_REGISTRATION###'] = $this->getFieldHeader('registration');
		$markers['###REGISTRATION###'] = $this->getFieldContent('registration');

		$markers['###BACK###'] = $this->pi_list_linkSingle($this->pi_getLL('back', 'Back'), 0);

		return $this->cObj->substituteMarkerArrayCached($this->templateCache['SINGLE_VIEW'], $markers, $this->subpartsToHide);
	}
	
	/**
	 * Returns a list header row as a TR.
	 * Columns listed in $this->columnsToHide are hidden (ie. not displayed).
	 *
	 * @return	string		HTML output, a table row with a class attribute set
	 * 
	 * @access protected
	 */
	function pi_list_header() {
		$markers = array();
		$markers['###HEADER_CLASS###']      = $this->pi_classParam('listrow-header');
		$markers['###HEADER_TITLE###']      = $this->getFieldHeader_sortLink('title');
		$markers['###HEADER_DATE###']       = $this->getFieldHeader_sortLink('date');
		$markers['###HEADER_PRICE###']      = $this->getFieldHeader_sortLink('price_regular');
		$markers['###HEADER_ORGANIZERS###'] = $this->getFieldHeader_sortLink('organizers');
		$markers['###HEADER_VACANCIES###']  = $this->getFieldHeader('vacancies');

		return $this->cObj->substituteMarkerArrayCached($this->templateCache['LIST_HEADER'], $markers, $this->subpartsToHide);
	}

	/**
	 * Returns a list row as a TR. Get data from $this->internal['currentRow'];
	 * Columns listed in $this->columnsToHide are hidden (ie. not displayed).
	 *
	 * @param	integer		Row counting. Starts at 0 (zero). Used for alternating class values in the output rows.
	 * 
	 * @return	string		HTML output, a table row with a class attribute set (alternative based on odd/even rows)
	 * 
	 * @access protected
	 */
	function pi_list_row($c) {
		$markers = array();
		$markers['###ITEM_CLASS###']       = ($c % 2) ? ' class="listrow-odd"' : '';
		$markers["###TITLE_LINK###"]       = $this->getFieldContent('title');
		$markers["###CLASS_TITLE###"]      = $this->pi_getClassName('title');
		$markers["###DATE###"]             = $this->getFieldContent('date');
		$markers["###CLASS_DATE###"]       = $this->pi_getClassName('date');
		$markers["###PRICE###"]            = $this->getFieldContent('price_regular');
		$markers["###CLASS_PRICE###"]      = $this->pi_getClassName('price_regular');
		$markers["###ORGANIZERS###"]       = $this->getFieldContent('organizers');
		$markers["###CLASS_ORGANIZERS###"] = $this->pi_getClassName('organizer');
		$markers["###VACANCIES###"]        = $this->getFieldContent('vacancies')
			.'&nbsp;&nbsp;<span class="'.$this->pi_getClassName('square').'">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
		$markers["###CLASS_VACANCIES###"]  = $this->getVacanciesClasses();

		return $this->cObj->substituteMarkerArrayCached($this->templateCache['LIST_ITEM'], $markers, $this->subpartsToHide);
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
							$result = $beingDateDay.$result;
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
					$result = $beginDateTime.'&#8211;'.$endDateTime.'&nbsp;'.$this->pi_getLL('hours');
				} else {
					$result = '<em>'.$this->pi_getLL('willBeAnnounced').'</em>';
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
						$result = $this->pi_getLL('noVacancies');
					} else {
						$organizers = explode(',', $this->internal['currentRow']['organizers']);
						foreach($organizers as $currentOrganizer) {
							$result = '<a href="'.$this->organizersCache[$currentOrganizer]['registration_page']. 										'&subject=Anmeldung+zum+Seminar+'.urlencode($this->internal['currentRow']['title']).' '.urlencode($this->getFieldContent('begin_date')).'">'. $this->pi_getLL('online_registration').'</a>';
						}
					}
				} else {
					$result = $this->pi_getLL('noRegistrationNecessary');
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
		switch($fN) {
			case 'title':
			return $this->pi_getLL('listFieldHeader_title', '<em>title</em>');
			break;
			default:
			return $this->pi_getLL('listFieldHeader_'.$fN, '['.$fN.']');
			break;
		}
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

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']);
}

?>