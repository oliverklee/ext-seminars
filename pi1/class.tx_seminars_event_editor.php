<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2007 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('ameos_formidable').'api/class.tx_ameosformidable.php');

class tx_seminars_event_editor extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_event_editor';

	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_event_editor.php';

	/** the pi1 object where this event editor will be inserted */
	var $plugin;

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
	 * After the constructor has been called, hasAccess() (or hasAccessMessage())
	 * must be called to ensure that the logged-in user is allowed to edit a
	 * given seminar.
	 *
	 * @param	object		the pi1 object where this event editor will be inserted (must not be null)
	 *
	 * @access	public
	 */
	function tx_seminars_event_editor(&$plugin) {
		$this->plugin =& $plugin;
		$this->init($this->plugin->conf);

		$this->sTemplatePath = $this->plugin->getConfValueString(
			'templateFile',
			's_template_special',
			true
		);
		// Edit an existing record or create a new one?
		$this->iEdition = (array_key_exists('action', $this->plugin->piVars)
			&& $this->plugin->piVars['action'] == 'EDIT')
			&& (intval($this->plugin->piVars['seminar']) > 0)
			? intval($this->plugin->piVars['seminar']) : false;

		// execute record level events thrown by formidable, such as DELETE
		$this->_doEvents();

		// initialize the creation/edition form
		$this->_initForms();

		return;
	}

	/**
	 * Processes events in the form like adding or editing an event.
	 *
	 * Currently, this function currently is a no-op.
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
			t3lib_extmgm::extPath($this->extKey).'pi1/event_editor.xml',
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
	 * Provides data items for the list of available event types.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 *
	 * @return	array		$items with additional items from the event_types table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListEventTypes($items) {
		return $this->populateList($items, $this->tableEventTypes);
	}

	/**
	 * Provides data items for the list of available payment methods.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 *
	 * @return	array		$items with additional items from payment_methods table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListPaymentMethods($items) {
		return $this->populateList($items, $this->tablePaymentMethods);
	}

	/**
	 * Provides data items for the list of available organizers.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 *
	 * @return	array		$items with additional items from the organizers table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListOrganizers($items) {
		return $this->populateList($items, $this->tableOrganizers);
	}

	/**
	 * Provides data items for the list of available places.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 *
	 * @return	array		$items with additional items from the places table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListPlaces($items) {
		return $this->populateList($items, $this->tableSites);
	}

	/**
	 * Provides data items for the list of available speakers.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null)
	 *
	 * @return	array		$items with additional items from the speakers table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListSpeakers($items) {
		return $this->populateList($items, $this->tableSpeakers);
	}

	/**
	 * Gets the PID of the page where FE-created events will be stored.
	 *
	 * @return	integer		the PID of the page where FE-created events will be stored
	 *
	 * @access	public
	 */
	function getPidForNewEvents() {
		return $this->plugin->getConfValueInteger(
			'createEventsPID',
			's_fe_editing'
		);
	}

	/**
	 * Gets the URL of the page that should be displayed when an event has been
	 * successfully created.
	 *
	 * @return	string		complete URL of the FE page with a message
	 *
	 * @access	public
	 */
	function getEventSuccessfullySavedUrl() {
		// We need to manually combine the base URL and the path to the page to
		// redirect to. Without the baseURL as part of the returned URL, the
		// combination of formidable and realURL will lead us into troubles with
		// not existing URLs (and thus showing errors to the user).
		$pageId = $this->plugin->getConfValueInteger(
			'eventSuccessfullySavedPID',
			's_fe_editing'
		);
		$baseUrl = $this->getConfValueString('baseURL');
		$redirectPath = $this->plugin->pi_getPageLink(
			$pageId
		);

		return $baseUrl.$redirectPath;
	}

	/**
	 * Checks whether the currently logged-in FE user (if any) belongs to the
	 * FE group that is allowed to enter and edit event records in the FE.
	 * This group can be set using plugin.tx_seminars.eventEditorFeGroupID.
	 *
	 * If the "seminar" piVar is set, it also is checked whether that event
	 * record exists and the logged-in FE user is the owner.
	 *
	 * @return	boolean		true if a user is logged in and allowed to enter and edit events (especially the event given in the piVar "seminar"), false otherwise
	 *
	 * @access	public
	 */
	function hasAccess() {
		$isOkay = $this->isLoggedIn()
			&& isset($GLOBALS['TSFE']->fe_user->groupData['uid'][
				$this->plugin->getConfValueInteger(
					'eventEditorFeGroupID',
					's_fe_editing'
				)
			]
		);
		$seminarUid = (isset($this->plugin->piVars['seminar'])
			&& (array_key_exists('action', $this->plugin->piVars)
			&& $this->plugin->piVars['action'] == 'EDIT'))
			? intval($this->plugin->piVars['seminar']) : 0;

		// Only do the DB query if we are okay so far and an event UID has
		// been provided for editing.
		if ($isOkay && $seminarUid) {
			if (tx_seminars_objectfromdb::recordExists($seminarUid, $this->tableSeminars)) {
				/** Name of the seminar class in case someone subclasses it. */
				$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
				$seminar =& new $seminarClassname($seminarUid);
				$isOkay = $seminar->isOwnerFeUser();
				unset($seminar);
			} else {
				// Deny access if the seminar UID is incorrect.
				$isOkay = false;
			}
		}

		return $isOkay;
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
