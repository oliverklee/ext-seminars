<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_registration_editor' for the 'seminars' extension.
 *
 * This class is a controller which allows to create registrations on the FE.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('ameos_formidable').'api/class.tx_ameosformidable.php');

class tx_seminars_registration_editor extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_registration_editor';

	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_registration_editor.php';

	/** the pi1 object where this event editor will be inserted */
	var $plugin;

	/** Formidable object that creates the edit form. */
	var $oForm = null;

	/** the UID of the registration to edit (or false (not 0!) if we are creating an event) */
	var $iEdition = false;

	// Currently, we can only edit registration (attendance) records.
	/** the table to edit (without the extension prefix) */
	var $sEntity = 'attandances';

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;

	/** the seminar for which the user wants to register */
	var $seminar;

	/** the names of the form fields to show (with the keys being the same as
	 *  the values for performance reasons */
	var $formFieldsToShow = array();

	/**
	 * The constructor.
	 *
	 * This class may only be instantiated after is has already been made sure
	 * that the logged-in user is allowed to register for the corresponding
	 * event (or edit a registration).
	 *
	 * @param	object		the pi1 object where this registration editor will be inserted (must not be null)
	 *
	 * @access	public
	 */
	function tx_seminars_registration_editor(&$plugin) {
		$this->plugin =& $plugin;
		$this->registrationManager =& $plugin->registrationManager;
		$this->seminar =& $plugin->seminar;

		$this->init($this->plugin->conf);

		$formFieldsToShow = explode(',',
			$this->getConfValueString(
				'showRegistrationFields', 's_template_special'
			)
		);
		foreach ($formFieldsToShow as $currentFormField) {
			$trimmedFormField = trim($currentFormField);
			if (!empty($trimmedFormField)) {
				$this->formFieldsToShow[$trimmedFormField] = $trimmedFormField;
			}
		}

		// Currently, only new registrations can be entered.
		$this->iEdition = false;

		// execute record level events thrown by formidable, such as DELETE
		$this->_doEvents();

		// initialize the creation/edition form
		$this->_initForms();

		return;
	}

	/**
	 * Processes events in the form like adding or editing a registration.
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
			t3lib_extmgm::extPath($this->extKey) . 'pi1/registration_editor.xml',
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
	 * Processes the an entered/edited registration and stores it in the DB.
	 *
	 * @param	array		the entered form data with the field names as array keys (including the submit button ...)
	 *
	 * @access	public
	 */
	function processRegistration($parameters) {
		if ($this->registrationManager->canCreateRegistration(
				$this->seminar,
				$parameters)
		) {
			$this->registrationManager->createRegistration(
				$this->seminar,
				$parameters,
				$this->plugin
			);
		}

		return;
	}

	/**
	 * Checks whether there are at least $numberOfSeats available.
	 *
	 * @param	string		string representation of a number of seats to check for
	 *
	 * @return	boolean		true if there are at least $numberOfSeats seats available, false otherwise
	 *
	 * @access	public
	 */
	function canRegisterSeats($numberOfSeats) {
		$intNumberOfSeats = intval($numberOfSeats);
		return $this->registrationManager->canRegisterSeats(
			$this->seminar, $intNumberOfSeats
		);
	}

	/**
	 * Checks whether a checkbox is checked.
	 *
	 * @param	integer		the current value of the checkbox (0 or 1)
	 *
	 * @return	boolean		true if the checkbox is checked, false otherwise
	 *
	 * @access	public
	 */
	function isChecked($checkboxValue) {
		return (boolean) $checkboxValue;
	}

	/**
	 * Checks whether a form field should be displayed (and evaluated) at all.
	 *
	 * @oaram	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 * @param	object		the current FORMidable object
	 *
	 * @return	boolean		true if the current form field should be displayed, false otherwise
	 */
	function hasRegistrationFormField($parameters, $form) {
		return isset($this->formFieldsToShow[$parameters['elementname']]);
	}

	/**
	 * Gets the URL of the page that should be displayed after a user has
	 * signed up for an event.
	 *
	 * @param	array		optional third parameter to the _callUserObj function (unused)
	 * @param	array		contents of the "param" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	string		URL of the FE page with a message
	 *
	 * @access	public
	 */
	function getThankYouAfterRegistrationUrl() {
		$pageId = $this->plugin->getConfValueInteger(
			'thankYouAfterRegistrationPID',
			's_registration'
		);

		// On updates, the page ID might still not be set. Use the event list
		// for the meantime so the registration form won't be displayed again.
		if (!$pageId) {
			$pageId = $this->plugin->getConfValueInteger('listPID', 'sDEF');
		}

		return $this->plugin->pi_getPageLink($pageId);
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
	function populateListPaymentMethods($items, $params, &$form) {
		return $this->populateList(
			$items,
			$this->tablePaymentMethods,
			'uid IN ('.$this->seminar->getPaymentMethodsUids().')'
		);
	}

	/**
	 * Checks whether the methods of payment should be displayed at all,
	 * ie. whether they are enable in the setup and the current event actually
	 * has any payment methods assigned.
	 *
	 * @oaram	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 * @param	object		the current FORMidable object
	 *
	 * @return	boolean		true if the payment methods should be displayed, false otherwise
	 */
	function showMethodsOfPayment($parameters, $form) {
		return $this->seminar->hasPaymentMethods()
			&& $this->hasRegistrationFormField(
				array('elementname' => 'method_of_payment'),
				null
			);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_registration_editor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_registration_editor.php']);
}

?>
