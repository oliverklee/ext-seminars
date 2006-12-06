<?php
/***************************************************************
* Copyright notice
*
* (c) 2006 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_event_editor' for the 'seminars' extension.
 *
 * This class is a controller which allows to create and edit events on the FE.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_configgetter.php');
require_once(t3lib_extMgm::extPath('ameos_formidable').'api/class.tx_ameosformidable.php');

class tx_seminars_event_editor extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_event_editor';

	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_event_editor.php';

	/** the pi1 object where this event editor will be inserted */
	var $plugin;

	/** This objects provides access to config values in plugin.tx_seminars. */
	var $configGetter;

	/** Formidable object that creates the edit form. */
	var $oForm = null;

	/** the UID of the event to edit (or false (not 0!) if we are creating an event) */
	var $iEdition = false;

	/** path to the HTML template */
	var $sTemplatePath = '';

	// Currently, we can only edit event (seminar) records.
	/** the table to edit (without the extension prefix) */
	var $sEntity = 'seminars';

	/**
	 * The constructor.
	 *
	 * @param	object		the pi1 object where this event editor will be inserted (must not be null)
	 *
	 * @access	public
	 */
	function tx_seminars_event_editor(&$plugin) {
		$this->plugin =& $plugin;
		$this->init($this->plugin->conf);

		$this->configGetter =& t3lib_div::makeInstance('tx_seminars_configgetter');
		$this->configGetter->init();

		$this->sTemplatePath = $this->plugin->getConfValueString(
			'templateFile',
			's_template_special',
			true
		);
		// Edit an existing record or create a new one?
		/*
		$this->iEdition = (array_key_exists('event', $this->plugin->piVars)
			&& $this->plugin->piVars['event'] == 'EDIT')
			? intval($this->piVars['uid']) : false;
		*/
		// Currently, we can only create new events.
		$this->iEdition = false;

		// execute record level events thrown by formidable, such as DELETE
		$this->_doEvents();

		// initialize the creation/edition Form and the lister form
		$this->_initForms();

		return;
	}

	/**
	 * Processes events in the form like adding or editing an event.
	 *
	 * As editing and deleting events is not implemented yet, this function
	 * currently is a no-op.
	 *
	 * @access	protected
	 */
	function _doEvents() {
		return;
	}

	/**
	 * Initializes the create/edit form.
	 *
	 * @access	protected
	 */
	function _initForms() {
		$this->oForm =& t3lib_div::makeInstance('tx_ameosformidable');

		$this->oForm->init(
			$this,
			t3lib_extmgm::extPath($this->extKey) . 'pi1/event_editor.xml',
			$this->iEdition
		);

		return;
	}

	/**
	 * Creates the HTML output.
	 *
	 * @return 	string		HTML of the create/edit form
	 *
	 * @access	public
	 */
	function _render() {
		return $this->oForm->_render();
	}

	/**
	 * Provides data items from the DB.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 * @param	string		the table name to query
	 *
	 * @return	array		$items with additional items from the $params['what'] table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateList($items, $tableName) {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$tableName,
				'1=1'
				.t3lib_pageSelect::enableFields($tableName),
			'',
			'title',
			'');

		if ($dbResult) {
			while ($dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$items[] = array(
					'caption'	=> $dbResultRow['title'],
					'value'		=> $dbResultRow['uid']
				);
			}
		}

		// Reset the array pointer as the populateList* functions expect
		// arrays with a reset array pointer.
		reset($items);

		return $items;
	}

	/**
	 * Provides data items for the list of available event types.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		$items with additional items from the event_types table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListEventTypes($items, $params, &$form) {
		return $this->populateList($items, $this->tableEventTypes);
	}

	/**
	 * Provides data items for the list of available payment methods.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		$items with additional items from payment_methods table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListPaymentMethods($items, $params, &$form) {
		return $this->populateList($items, $this->tablePaymentMethods);
	}

	/**
	 * Provides data items for the list of available organizers.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		$items with additional items from the organizers table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListOrganizers($items, $params, &$form) {
		return $this->populateList($items, $this->tableOrganizers);
	}

	/**
	 * Provides data items for the list of available places.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		$items with additional items from the places table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListPlaces($items, $params, &$form) {
		return $this->populateList($items, $this->tableSites);
	}

	/**
	 * Provides data items for the list of available speakers.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		$items with additional items from the speakers table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListSpeakers($items, $params, &$form) {
		return $this->populateList($items, $this->tableSpeakers);
	}

	/**
	 * Gets the PID of the page where FE-created events will be stored.
	 *
	 * @param	array		optional third parameter to the _callUserObj function (unused)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	integer		the PID of the page where FE-created events will be stored
	 *
	 * @access	public
	 */
	function getPidForNewEvents($items, $params, &$form) {
		return $this->plugin->getConfValueInteger(
			'createEventsPID',
			's_fe_editing'
		);
	}

	/**
	 * Gets the URL of the page that should be displayed when an event has
	 * been successfully created.
	 *
	 * @param	array		optional third parameter to the _callUserObj function (unused)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	string		URL of the FE page with a message
	 *
	 * @access	public
	 */
	function getEventSuccessfullySavedUrl() {
		return $this->plugin->pi_getPageLink(
			$this->plugin->getConfValueInteger(
				'eventSuccessfullySavedPID',
				's_fe_editing')
		);
	}

	/**
	 * Gets the date and time format provided via TS setup.
	 *
	 * @param	array		optional third parameter to the _callUserObj function (unused)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	string		date and time format as provided via TS setup
	 *
	 * @access	public
	 */
	function getDateFormat() {
		return $this->configGetter->getConfValueString('dateFormatYMD').' '
			.$this->configGetter->getConfValueString('timeFormat');
	}

	/**
	 * Checks whether the currently logged-in FE user (if any) belongs to the
	 * FE group that is allowed to enter and edit event records in the FE.
	 * This group can be set using plugin.tx_seminars.eventEditorFeGroupID.
	 *
	 * @return	boolean		true if a user is logged in and allowed to enter and edit events, false otherwise
	 *
	 * @access	public
	 */
	function hasAccess() {
		return $this->isLoggedIn()
			&& isset($GLOBALS['TSFE']->fe_user->groupData['uid'][
				$this->plugin->getConfValueInteger(
					'eventEditorFeGroupID',
					's_fe_editing'
				)
			]
		);
	}

	/**
	 * Checks whether the currently logged-in FE user (if any) belongs to the
	 * FE group that is allowed to enter and edit event records in the FE.
	 * This group can be set using plugin.tx_seminars.eventEditorFeGroupID.
	 * If the FE user does not have the necessary permissions, a localized error
	 * message will be returned.
	 *
	 * @return	string		an empty string if a user is logged in and allowed to enter and edit events, a localized error message otherwise
	 *
	 * @access	public
	 */
	function hasAccessMessage() {
		$result = '';

		if (!$this->hasAccess()) {
			$this->plugin->setMarkerContent(
				'error_text',
				$this->plugin->pi_getLL('message_noAccessToEventEditor')
			);
			$result = $this->plugin->substituteMarkerArrayCached('ERROR_VIEW');
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_event_editor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_event_editor.php']);
}

?>
