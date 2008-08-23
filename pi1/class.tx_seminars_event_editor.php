<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
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

		// Edit an existing record or create a new one?
		$this->iEdition = (array_key_exists('action', $this->plugin->piVars)
			&& $this->plugin->piVars['action'] == 'EDIT')
			&& (intval($this->plugin->piVars['seminar']) > 0)
			? intval($this->plugin->piVars['seminar']) : false;

		// initialize the creation/edition form
		$this->_initForms();
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
	}

	/**
	 * Gets the path to the HTML template as set in the TS setup or flexforms.
	 * The returned path will always be an absolute path in the file system;
	 * EXT: references will automatically get resolved.
	 *
	 * @return	string		the path to the HTML template as an absolute path in the file system, will not be empty in a correct configuration, will never be null
	 *
	 * @access	public
	 */
	function getTemplatePath() {
		return t3lib_div::getFileAbsFileName(
			$this->plugin->getConfValueString(
				'templateFile',
				's_template_special',
				true
			)
		);
	}

	/**
	 * Creates the HTML output.
	 *
	 * @return 	string		HTML of the create/edit form
	 *
	 * @access	public
	 */
	function _render() {
		$rawForm = $this->oForm->_render();
		$this->plugin->processTemplate($rawForm);
		$this->plugin->setLabels();

		return $this->plugin->getSubpart();
	}

	/**
	 * Provides data items for the list of available categories.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the categories
	 * 						table as an array with the keys "caption" (for the
	 * 						title) and "value" (for the UID)
	 */
	public function populateListCategories(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_CATEGORIES);
	}

	/**
	 * Provides data items for the list of available event types.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the event_types
	 * 						table as an array with the keys "caption" (for the
	 * 						title) and "value" (for the UID)
	 */
	public function populateListEventTypes(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_EVENT_TYPES);
	}

	/**
	 * Provides data items for the list of available lodgings.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the lodgings table
	 * 						as an array with the keys "caption" (for the title)
	 *						and "value" (for the UID)
	 */
	public function populateListLodgings(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_LODGINGS);
	}

	/**
	 * Provides data items for the list of available foods.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the foods table
	 * 						as an array with the keys "caption" (for the title)
	 *						and "value" (for the UID)
	 */
	public function populateListFoods(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_FOODS);
	}

	/**
	 * Provides data items for the list of available payment methods.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from payment methods
	 * 						table as an array with the keys "caption" (for the
	 *						title) and "value" (for the UID)
	 */
	public function populateListPaymentMethods(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_PAYMENT_METHODS);
	}

	/**
	 * Provides data items for the list of available organizers.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the organizers
	 * 						table as an array with the keys "caption" (for the
	 *						title) and "value" (for the UID)
	 */
	public function populateListOrganizers(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_ORGANIZERS);
	}

	/**
	 * Provides data items for the list of available places.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the places table
	 * 						as an array with the keys "caption" (for the title)
	 *						and "value" (for the UID)
	 */
	public function populateListPlaces(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_SITES);
	}

	/**
	 * Provides data items for the list of available speakers.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the speakers table
	 * 						as an array with the keys "caption" (for the title)
	 *						and "value" (for the UID)
	 */
	public function populateListSpeakers(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_SPEAKERS);
	}

	/**
	 * Provides data items for the list of available checkboxes.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the checkboxes
	 * 						table as an array with the keys "caption" (for the
	 *						title) and "value" (for the UID)
	 */
	public function populateListCheckboxes(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_CHECKBOXES);
	}

	/**
	 * Provides data items for the list of available target groups.
	 *
	 * @param	array		any pre-filled data (may be empty)
	 *
	 * @return	array		$items with additional items from the target groups
	 * 						table as an array with the keys "caption" (for the
	 *						title) and "value" (for the UID)
	 */
	public function populateListTargetGroups(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_TARGET_GROUPS);
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
	 */
	public function getEventSuccessfullySavedUrl() {
		$pageId = $this->plugin->getConfValueInteger(
			'eventSuccessfullySavedPID',
			's_fe_editing'
		);

		return t3lib_div::locationHeaderUrl(
			$this->plugin->cObj->typoLink_URL(array('parameter' => $pageId))
		);
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
			if (tx_seminars_objectfromdb::recordExists(
				$seminarUid,
				SEMINARS_TABLE_SEMINARS)
			) {
				/** Name of the seminar class in case someone subclasses it. */
				$seminarClassname = t3lib_div::makeInstanceClassName(
					'tx_seminars_seminar'
				);
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
			$this->plugin->setMarker(
				'error_text',
				$this->plugin->translate('message_noAccessToEventEditor')
			);
			$result = $this->plugin->getSubpart('ERROR_VIEW');
		}

		return $result;
	}

	/**
	 * Changes all potential decimal separators (commas and dots) in price
	 * fields to dots.
	 *
	 * @param	array		all entered form data with the field names as keys
	 *
	 * @return	array		the entered form data with all commas in all price fields changed to dots
	 *
	 * @access	public
	 */
	function unifyDecimalSeparators(&$formData) {
		$priceFields = array(
			'price_regular', 'price_regular_early', 'price_regular_board',
			'price_special', 'price_special_early', 'price_special_board'
		);

		foreach ($priceFields as $key) {
			if (isset($formData[$key])) {
				$formData[$key] = str_replace(',', '.', $formData[$key]);
			}
		}

		return $formData;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_event_editor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_event_editor.php']);
}
?>