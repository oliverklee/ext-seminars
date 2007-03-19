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
require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');

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

	/** whether we show the confirmation page (true) or the form (false) */
	var $isConfirmationPage = false;

	/** fields that are part of the billing address */
	var $fieldsInBillingAddress = array(
		'gender',
		'name',
		'address',
		'zip',
		'city',
		'country',
		'telephone',
		'email',
	);

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

		$xmlFile = (!$this->isConfirmationPage) ?
			'registration_editor.xml' : 'registration_editor_step2.xml';

		$this->oForm->init(
			$this,
			t3lib_extmgm::extPath($this->extKey).'pi1/'.$xmlFile,
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
		$result = $this->oForm->_render();
		// For the confirmation page, we need to reload the whole thing.
		if ($this->isConfirmationPage) {
			$this->_initForms();
			$result = $this->oForm->_render();
		}
		return $result;
	}

	/**
	 * Selects the confirmation page (the second step of the registration form)
	 * for display. This affects $this->_render().
	 *
	 * @param	array		the entered form data with the field names as array keys (including the submit button ...)
	 *
	 * @access	public
	 */
	function showConfirmationPage($parameters) {
		$this->isConfirmationPage = true;

		return;
	}

	/**
	 * Processes the entered/edited registration and stores it in the DB.
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
	 * Checks whether the "travelling terms" checkbox (ie. the second "terms"
	 * checkbox) is enabled in the event record *and* via TS setup.
	 *
	 * @return	boolean		true if the "travelling terms" checkbox is enabled in the event record *and* via TS setup, false otherwise
	 *
	 * @access	public
	 */
	function isTerms2Enabled() {
		return $this->hasRegistrationFormField(array('elementname' => 'terms_2'))
			&& $this->seminar->hasTerms2();
	}

	/**
	 * Checks whether the "terms_2" checkbox is checked (if it is enabled in the
	 * configuration). If the checkbox is disabled in the configuration, this
	 * function always returns true.
	 *
	 * @param	integer		the current value of the checkbox (0 or 1)
	 *
	 * @return	boolean		true if the checkbox is checked or disabled in the configuration, false if it is not checked AND enabled in the configuration
	 *
	 * @access	public
	 */
	function isTerms2CheckedAndEnabled($checkboxValue) {
		return ((boolean) $checkboxValue) || !$this->isTerms2Enabled();
	}

	/**
	 * Checks whether a method of payment is selected OR this event has no
	 * payment methods set at all OR the corresponding registration field is
	 * not visible in the registration form (in which case it is neither
	 * necessary nor possible to select any payment method) OR this event has
	 * no price at all.
	 *
	 * @param	mixed		the currently selected value (a positive integer) or null if no radiobutton is selected
	 *
	 * @return	boolean		true if a method of payment is selected OR no method could have been selected at all OR this event has no price, false if none is selected, but should have been selected
	 *
	 * @access	public
	 */
	function isMethodOfPaymentSelected($radiogroupValue) {
		return $this->isRadiobuttonSelected($radiogroupValue)
			|| !$this->seminar->hasPaymentMethods()
			|| !$this->seminar->hasAnyPrice()
			|| !$this->showMethodsOfPayment();
	}

	/**
	 * Checks whether a radiobutton in a radiobutton group is selected.
	 *
	 * @param	mixed		the currently selected value (a positive integer) or null if no button is selected
	 *
	 * @return	boolean		true if a radiobutton is selected, false if none is selected
	 *
	 * @access	public
	 */
	function isRadiobuttonSelected($radiogroupValue) {
		return (boolean) $radiogroupValue;
	}

	/**
	 * Checks whether a form field should be displayed (and evaluated) at all.
	 * This is specified via TS setup (or flexforms) using the
	 * "showRegistrationFields" variable.
	 *
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 *
	 * @return	boolean		true if the current form field should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function hasRegistrationFormField($parameters) {
		return isset($this->formFieldsToShow[$parameters['elementname']]);
	}

	/**
	 * Checks whether a form field should be displayed (and evaluated) at all.
	 * This is specified via TS setup (or flexforms) using the
	 * "showRegistrationFields" variable.
	 *
	 * This function also checks if the current event has a price set at all,
	 * and returns only true if the event has a price (ie. is not completely for
	 * free) and the current form field should be displayed.
	 *
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 *
	 * @return	boolean		true if the current form field should be displayed AND the current event is not completely for free, false otherwise
	 *
	 * @access	public
	 */
	function hasBankDataFormField($parameters) {
		return $this->hasRegistrationFormField($parameters)
			&& $this->seminar->hasAnyPrice();
	}

	/**
	 * Gets the URL of the page that should be displayed after a user has
	 * signed up for an event, but only if the form has been submitted from
	 * stage 2 (the confirmation page).
	 *
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 * @param	object		the current FORMidable object
	 *
	 * @return	string		complete URL of the FE page with a message (or null if the confirmation page has not been submitted yet)
	 *
	 * @access	public
	 */
	function getThankYouAfterRegistrationUrl($parameters, &$form) {
		$pageId = $this->plugin->getConfValueInteger(
			'thankYouAfterRegistrationPID',
			's_registration'
		);

		// On freshly updated sites, the configuration value might not be set
		// yet. To avoid breaking the site, we use the event list in this case
		// so the registration form won't be displayed again.
		if (!$pageId) {
			$pageId = $this->plugin->getConfValueInteger('listPID', 'sDEF');
		}

		// We need to manually combine the base URL and the path to the page to
		// redirect to. Without the baseURL as part of the returned URL, the
		// combination of formidable and realURL will lead us into troubles with
		// not existing URLs (and thus showing errors to the user).
		$baseUrl = $this->getConfValueString('baseURL');
		$redirectPath = $this->plugin->pi_getPageLink(
			$pageId,
			'',
			array('tx_seminars_pi1[showUid]' => $this->seminar->getUid())
		);

		return $baseUrl.$redirectPath;
	}

	/**
	 * Provides data items for the list of available places.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null, unused)
	 * @param	array		contents of the "params" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		items from the payment methods table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListPaymentMethods($items, $params, &$form) {
		$result = array();

		if ($this->seminar->hasPaymentMethods()) {
			$result = $this->populateList(
				$items,
				$this->tablePaymentMethods,
				'uid IN ('.$this->seminar->getPaymentMethodsUids().')'
			);
		}

		return $result;
	}

	/**
	 * Checks whether the methods of payment should be displayed at all,
	 * ie. whether they are enable in the setup and the current event actually
	 * has any payment methods assigned.
	 *
	 * @return	boolean		true if the payment methods should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function showMethodsOfPayment() {
		return $this->seminar->hasPaymentMethods()
			&& $this->hasRegistrationFormField(
				array('elementname' => 'method_of_payment')
			);
	}

	/**
	 * Gets the currently logged-in FE user's data nicely formatted as HTML so
	 * that it can be directly included on the confirmation page.
	 *
	 * @param	array		(unused)
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 * @param	object		the current FORMidable object
	 *
	 * @return	string		the currently logged-in FE user's data
	 *
	 * @access	public
	 */
	function getAllFeUserData($unused, $parameters, $form) {
		$userData = $GLOBALS['TSFE']->fe_user->user;

		foreach (array(
			'name',
			'company',
			'address',
			'zip',
			'city',
			'country',
			'telephone',
			'email'
		) as $currentKey) {
			$this->plugin->setMarkerContent(
				'user_'.$currentKey,
				htmlspecialchars($userData[$currentKey])
			);
		}
		return $this->plugin->substituteMarkerArrayCached(
			'REGISTRATION_CONFIRMATION_FEUSER'
		);
	}

	/**
	 * Gets the already entered registration data nicely formatted as HTML so
	 * that it can be directly included on the confirmation page.
	 *
	 * @param	array		(unused)
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 * @param	object		the current FORMidable object
	 *
	 * @return	string		the already entered registration data, nicely formatted as HTML
	 *
	 * @access	public
	 */
	function getRegistrationData($unused, $parameters, $form) {
		$result = '';

		$formData = $form->oDataHandler->__aFormData;
		$availablePaymentMethods = $this->populateListPaymentMethods(
			array(),
			null,
			$form
		);

		if (isset($formData['method_of_payment'])
			&& isset($availablePaymentMethods[$formData['method_of_payment']])) {
			$this->plugin->setMarkerContent(
				'registration_data_heading',
				$this->plugin->pi_getLL('label_selected_paymentmethod')
			);
			$this->plugin->setMarkerContent(
				'registration_data_body',
				$availablePaymentMethods[$formData['method_of_payment']]['caption']
			);
			$result .= $this->plugin->substituteMarkerArrayCached(
				'REGISTRATION_CONFIRMATION_DATA'
			);
		}

		$availablePrices = $this->seminar->getAvailablePrices();
		// If no (available) price is selected, use the first price by default.
		$selectedPrice = (isset($formData['price'])
			&& $this->seminar->isPriceAvailable($formData['price']))
			? $formData['price'] : key($availablePrices);
		$this->plugin->setMarkerContent(
			'registration_data_heading',
			$this->plugin->pi_getLL('label_price_general')
		);
		$this->plugin->setMarkerContent(
			'registration_data_body',
			$availablePrices[$selectedPrice]['caption']
		);
		$result .= $this->plugin->substituteMarkerArrayCached(
			'REGISTRATION_CONFIRMATION_DATA'
		);

		// Build the total price for this registration and add it to the form data
		// to show it on the confirmation page.
		// This value will not be saved to the database from here. It will be
		// calculated again when creating the registration object.
		// It will not be added if no total price can be calculated (e.g. total price = 0.00)
		if (isset($formData['seats']) && $formData['seats'] > 0) {
			$seats = $formData['seats'];
		} else {
			$seats = 1;
		}
		if ($availablePrices[$selectedPrice]['amount'] != '0.00') {
			$totalPrice = $this->seminar->formatPrice(
				$seats * $availablePrices[$selectedPrice]['amount']
			);
			$currency = $this->registrationManager->getConfValueString('currency');
			$formData['total_price'] = $totalPrice.' '.$currency;
		}

		foreach (array(
			'account_number',
			'bank_code',
			'bank_name',
			'account_owner',
			'seats',
			'total_price',
			'attendees_names',
			'interests',
			'expectations',
			'background_knowledge',
			'accommodation',
			'food',
			'known_from',
			'notes'
		) as $currentKey) {
			if (isset($formData[$currentKey]) && $formData[$currentKey] != '') {
				$this->plugin->setMarkerContent(
					'registration_data_heading',
					$this->plugin->pi_getLL('label_'.$currentKey)
				);
				$fieldContent = str_replace(
					chr(13),
					'<br />',
					htmlspecialchars($formData[$currentKey])
				);
				$this->plugin->setMarkerContent(
					'registration_data_body',
					$fieldContent
				);
				$result .= $this->plugin->substituteMarkerArrayCached(
					'REGISTRATION_CONFIRMATION_DATA'
				);
			}
		}

		return $result;
	}

	/**
	 * Gets the already entered billing address nicely formatted as HTML so
	 * that it can be directly included on the confirmation page.
	 *
	 * @param	array		(unused)
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 * @param	object		the current FORMidable object
	 *
	 * @return	string		the already entered registration data, nicely formatted as HTML
	 *
	 * @access	public
	 */
	function getBillingAddress($unused, $parameters, $form) {
		$result = '';

		$formData = $form->oDataHandler->__aFormData;

		foreach ($this->fieldsInBillingAddress as $currentKey) {
			$currentFormData = $formData[$currentKey];
			if (isset($formData[$currentKey]) && $formData[$currentKey] != '') {
				// If the gender field is hidden, it would have an empty value,
				// so we wouldn't be here. So let's convert the "gender" index
				// into a readable string.
				if ($currentKey == 'gender') {
					$currentFormData =
						$this->pi_getLL('label_gender.I.'.intval($currentFormData));
				}
				$processedFormData = str_replace(
					chr(13),
					'<br />',
					htmlspecialchars($currentFormData)
				);

				$result .= $processedFormData.'<br />';
			}
		}

		$this->plugin->setMarkerContent('registration_billing_address', $result);

		return $this->plugin->substituteMarkerArrayCached('REGISTRATION_CONFIRMATION_BILLING');
	}

	/**
	 * Checks whether the list of attendees' names is non-empty or less than two
	 * seats are requested or the field "attendees names" is not displayed.
	 *
	 * @param	string		the current value of the field with the attendees' names
	 * @param	object		the current FORMidable object
	 *
	 * @return	boolean		true if the field is non-empty or less than two seats are reserved or this field is not displayed at all, false otherwise
	 *
	 * @access	public
	 */
	function hasAttendeesNames($attendeesNames, &$form) {
		$dataHandler = $form->oDataHandler;
		$seats = isset($dataHandler->__aFormData['seats']) ?
			intval($dataHandler->__aFormData['seats']) : 1;

		return (!empty($attendeesNames) || ($seats < 2)
			|| !$this->hasRegistrationFormField(
				array('elementname' => 'attendees_names')
			)
		);
	}

	/**
	 * Checks whether the current field is non-empty if the payment method
	 * "bank transfer" is selected. If a different payment method is selected
	 * (or none is defined as "bank transfer"), the check is always positive and
	 * returns true.
	 *
	 * @param	string		the value of the current field
	 * @param	object		the current FORMidable object
	 *
	 * @return	boolean		true if the field is non-empty or "bank transfer" is not selected
	 *
	 * @access	public
	 */
	function hasBankData($bankData, &$form) {
		$result = true;

		if (empty($bankData)) {
			$bankTransferUid = $this->plugin->getConfValueInteger('bankTransferUID');

			$dataHandler = $form->oDataHandler;
			$paymentMethod = isset($dataHandler->__aFormData['method_of_payment']) ?
				intval($dataHandler->__aFormData['method_of_payment']) : 0;

			if ($bankTransferUid && ($paymentMethod == $bankTransferUid)) {
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Returns a data item of the currently logged-in FE user.
	 *
	 * This function may only be called when a FE user is logged in.
	 *
	 * The caller needs to take care of htmlspecialcharing the data.
	 *
	 * @param	array		array that contains any pre-filled data (unused)
	 * @param	array		contents of the "params" XML child of the userrobj node (needs to contain an element with the key "key")
	 *
	 * @return	string		the contents of the element
	 *
	 * @access	public
	 */
	function getFeUserData($items, $params) {
		$feUserData = $GLOBALS['TSFE']->fe_user->user;
		return $feUserData[$params['key']];
	}

	/**
	 * Provides a localized list of country names from static_tables.
	 *
	 * @param	array		array that contains any pre-filled data (unused)
	 * @param	array		contents of the "params" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		a list of localized country names from static_tables as an array with the keys "caption" (for the title) and "value" (in this case, the same as the caption)
	 *
	 * @access	public
	 */
	function populateListCountries($items, $params, &$form) {
		$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
		$this->staticInfo->init();
		$allCountries = $this->staticInfo->initCountries();
		$result = array();
		// Add an empty item at the top so we won't have Afghanistan (the first
		// entry) pre-selected for empty values.
		$result[] = array(
			'caption' => ' ',
			'value' => ''
		);

		foreach ($allCountries as $currentCountry) {
			$result[] = array(
				'caption' => $currentCountry,
				'value' => $currentCountry
			);
		}

		return $result;
	}

	/**
	 * Provides data items for the list of option checkboxes for this event.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null, unused)
	 * @param	array		contents of the "params" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		items from the checkboxes table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateCheckboxes($items, $params, &$form) {
		$result = array();

		if ($this->seminar->hasCheckboxes()) {
			$result = $this->seminar->getCheckboxes();
		}

		return $result;
	}

	/**
	 * Checks whether our current event has any option checkboxes AND the
	 * checkboxes should be displayed at all.
	 *
	 * @return	boolean		true if we have a non-empty list of checkboxes AND this list should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function hasCheckboxes() {
		return $this->seminar->hasCheckboxes()
			&& $this->hasRegistrationFormField(
				array('elementname' => 'checkboxes')
			);
	}

	/**
	 * Provides data items for the list of lodging options for this event.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null, unused)
	 * @param	array		contents of the "params" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		items from the lodgings table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateLodgings($items, $params, &$form) {
		$result = array();

		if ($this->seminar->hasLodgings()) {
			$result = $this->seminar->getLodgings();
		}

		return $result;
	}

	/**
	 * Checks whether at least one lodging option is selected (if there is at
	 * least one lodging option for this event and the lodging options should
	 * be displayed).
	 *
	 * @param	string		the value of the current field
	 * @param	object		the current FORMidable object
	 *
	 * @return	boolean		true if at least one item is selected or no lodging options can be selected
	 *
	 * @access	public
	 */
	function isLodgingSelected($selection, &$form) {
		return !empty($selection) || !$this->hasLodgings();
	}

	/**
	 * Checks whether our current event has any lodging options and the
	 * lodging options should be displayed at all.
	 *
	 * @return	boolean		true if we have a non-empty list of lodging options and this list should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function hasLodgings() {
		return $this->seminar->hasLodgings()
			&& $this->hasRegistrationFormField(
				array('elementname' => 'lodgings')
			);
	}

	/**
	 * Provides data items for the list of food options for this event.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null, unused)
	 * @param	array		contents of the "params" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		items from the foods table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateFoods($items, $params, &$form) {
		$result = array();

		if ($this->seminar->hasFoods()) {
			$result = $this->seminar->getFoods();
		}

		return $result;
	}

	/**
	 * Checks whether our current event has any food options and the food
	 * options should be displayed at all.
	 *
	 * @return	boolean		true if we have a non-empty list of food options and this list should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function hasFoods() {
		return $this->seminar->hasFoods()
			&& $this->hasRegistrationFormField(
				array('elementname' => 'foods')
			);
	}

	/**
	 * Checks whether at least one food option is selected (if there is at
	 * least one food option for this event and the food options should
	 * be displayed).
	 *
	 * @param	string		the value of the current field
	 * @param	object		the current FORMidable object
	 *
	 * @return	boolean		true if at least one item is selected or no food options can be selected
	 *
	 * @access	public
	 */
	function isFoodSelected($selection, &$form) {
		return !empty($selection) || !$this->hasFoods();
	}

	/**
	 * Provides data items for the prices for this event.
	 *
	 * @param	array		array that contains any pre-filled data (unused)
	 * @param	array		contents of the "params" XML child of the userrobj node (unused)
	 * @param	object		the current renderlet XML node as a recursive array (unused)
	 *
	 * @return	array		available prices as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populatePrices($items, $params, &$form) {
		return $this->seminar->getAvailablePrices();
	}

	/**
	 * Checks whether a valid price is selected or the "price" registration
	 * field is not visible in the registration form (in which case it is not
	 * possible to select a price).
	 *
	 * @param	mixed		the currently selected value (a positive integer) or null if no radiobutton is selected
	 *
	 * @return	boolean		true if a valid price is selected or the price field is hidden, false if none is selected, but could have been selected
	 *
	 * @access	public
	 */
	function isValidPriceSelected($radiogroupValue) {
		return $this->seminar->isPriceAvailable($radiogroupValue)
			|| !$this->hasRegistrationFormField(
				array('elementname' => 'price')
			);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_registration_editor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_registration_editor.php']);
}

?>
