<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_templatehelper.php');

require_once(t3lib_extMgm::extPath('ameos_formidable') . 'api/class.tx_ameosformidable.php');
require_once(t3lib_extMgm::extPath('static_info_tables') . 'pi1/class.tx_staticinfotables_pi1.php');

class tx_seminars_registration_editor extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_registration_editor';

	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_seminars_registration_editor.php';

	/** the pi1 object where this event editor will be inserted */
	var $plugin;

	/** Formidable object that creates the edit form. */
	var $oForm = null;

	/**
	 * the UID of the registration to edit (or false (not 0!) if we are creating
	 * an event)
	 */
	var $iEdition = false;

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;

	/** the seminar for which the user wants to register */
	var $seminar;

	/** the names of the form fields to show (with the keys being the same as
	 *  the values for performance reasons */
	var $formFieldsToShow = array();

	/**
	 * the number of the current page of the form (starting with 0 for the first
	 * page)
	 */
	var $currentPageNumber = 0;

	/**
	 * fields that are part of the billing address, with the value controlling
	 * if the field will be displayed with a label on the second page of the
	 * registration form
	 */
	var $fieldsInBillingAddress = array(
		'gender' => false,
		'name' => false,
		'address' => false,
		'zip' => false,
		'city' => false,
		'country' => false,
		'telephone' => true,
		'email' => true
	);

	/** an instance of tx_staticinfotables_pi1 */
	var $staticInfo = null;

	/**
	 * The constructor.
	 *
	 * This class may only be instantiated after is has already been made sure
	 * that the logged-in user is allowed to register for the corresponding
	 * event (or edit a registration).
	 *
	 * @param	tx_seminars_pi1		the pi1 object where this registration
	 * 								editor will be inserted
	 */
	public function __construct(tx_seminars_pi1 $plugin) {
		$this->plugin = $plugin;
		$this->cObj = $plugin->cObj;
		$this->registrationManager = $plugin->getRegistrationManager();
		$this->seminar = $plugin->getSeminar();

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

		switch ($this->plugin->piVars['action']) {
			case 'unregister':
				$xmlFile = 'registration_editor_unregistration.xml';
				break;
			case 'register':
				// The fall-through is intended.
				switch ($this->currentPageNumber) {
					case 1:
						$xmlFile = 'registration_editor_step2.xml';
						break;
					case 0:
						// The fall-through is intended.
					default;
						$xmlFile = 'registration_editor_step1.xml';
						break;
				}
				break;
		}

		$this->oForm->init(
			$this,
			t3lib_extmgm::extPath($this->extKey).'pi1/'.$xmlFile,
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
				'registrationEditorTemplateFile',
				's_registration',
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
		// For the confirmation page, we need to reload the whole thing. Yet,
		// the previous rendering still is necessary for processing the data.
		if ($this->currentPageNumber > 0) {
			$this->_initForms();
			$rawForm = $this->oForm->_render();
		}

		// Remove empty label tags that have been created due to a bug in
		// FORMidable.
		$rawForm = preg_replace('/<label[^>]*><\/label>/', '', $rawForm);
		$this->processTemplate($rawForm);
		$this->setLabels();
		$this->hideUnusedFormFields();

		return $this->getSubpart('', 2);
	}

	/**
	 * Selects the confirmation page (the second step of the registration form)
	 * for display. This affects $this->_render().
	 *
	 * @param	array		the entered form data with the field names as array keys (including the submit button)
	 *
	 * @access	public
	 */
	function setPage(array $parameters) {
		$this->currentPageNumber = $parameters['next_page'];
	}

	/**
	 * Checks whether we are on the last page of the registration form and we
	 * can proceed to saving the registration.
	 *
	 * @return	boolean		true if we can proceed to saving the registration, false otherwise
	 *
	 * @access	public
	 */
	function isLastPage() {
		return ($this->currentPageNumber == 2);
	}

	/**
	 * Processes the entered/edited registration and stores it in the DB.
	 *
	 * In addition, the entered payment data is stored in the FE user session.
	 *
	 * @param	array		the entered form data with the field names as array keys (including the submit button ...)
	 *
	 * @access	public
	 */
	function processRegistration(array $parameters) {
		$this->saveDataToSession($parameters);

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
	 * Checks whether a checkbox is checked OR the "finish registration" button
	 * hasn't just been clicked.
	 *
	 * @param	integer		the current value of the checkbox (0 or 1)
	 *
	 * @return	boolean		true if the checkbox is checked or we are not on the confirmation page, false otherwise
	 *
	 * @access	public
	 */
	function isTermsChecked($checkboxValue) {
		return ((boolean) $checkboxValue) || ($this->currentPageNumber != 2);
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
	 * function always returns true. It also always returns true if the
	 * "finish registration" button hasn't just been clicked.
	 *
	 * @param	integer		the current value of the checkbox (0 or 1)
	 *
	 * @return	boolean		true if the checkbox is checked or disabled in the configuration or if the "finish registration" button hasn't just been clicked, false if it is not checked AND enabled in the configuration
	 *
	 * @access	public
	 */
	function isTerms2CheckedAndEnabled($checkboxValue) {
		return ((boolean) $checkboxValue) || !$this->isTerms2Enabled()
			|| ($this->currentPageNumber != 2);
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
	function hasRegistrationFormField(array $parameters) {
		return isset($this->formFieldsToShow[$parameters['elementname']]);
	}

	/**
	 * Checks whether a form field should be displayed (and evaluated) at all.
	 * This is specified via TS setup (or flexforms) using the
	 * "showRegistrationFields" variable.
	 *
	 * In addition, this function takes into account whether the form field
	 * actually has any meaningful content.
	 * Example: The payment methods field will be disabled if the current event
	 * does not have any payment methods.
	 *
	 * After some refactoring, this function will replace the function
	 * hasRegistrationFormField.
	 *
	 * @param	string		the key of the field to test, must not be empty
	 *
	 * @return	boolean		true if the current form field should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function isFormFieldEnabled($key) {
		// Some containers cannot be enabled or disabled via TS setup, but
		// are containers and depend on their content being displayed.
		switch ($key) {
			case 'payment':
				$result = $this->isFormFieldEnabled('price')
					|| $this->isFormFieldEnabled('method_of_payment')
					|| $this->isFormFieldEnabled('banking_data');
				break;
			case 'banking_data':
				$result = $this->isFormFieldEnabled('account_number')
					|| $this->isFormFieldEnabled('account_owner')
					|| $this->isFormFieldEnabled('bank_code')
					|| $this->isFormFieldEnabled('bank_name');
				break;
			case 'billing_address':
				// This fields actually can also be disabled via TS setup.
				$result = isset($this->formFieldsToShow[$key])
					&& (
						$this->isFormFieldEnabled('gender')
						|| $this->isFormFieldEnabled('name')
						|| $this->isFormFieldEnabled('address')
						|| $this->isFormFieldEnabled('zip')
						|| $this->isFormFieldEnabled('city')
						|| $this->isFormFieldEnabled('country')
						|| $this->isFormFieldEnabled('telephone')
						|| $this->isFormFieldEnabled('email')
					);
				break;
			case 'more_seats':
				$result = $this->isFormFieldEnabled('seats')
					|| $this->isFormFieldEnabled('attendees_names')
					|| $this->isFormFieldEnabled('kids');
				break;
			case 'lodging_and_food':
				$result = $this->isFormFieldEnabled('lodgings')
					|| $this->isFormFieldEnabled('accommodation')
					|| $this->isFormFieldEnabled('foods')
					|| $this->isFormFieldEnabled('food');
				break;
			case 'additional_information':
				$result = $this->isFormFieldEnabled('checkboxes')
					|| $this->isFormFieldEnabled('interests')
					|| $this->isFormFieldEnabled('expectations')
					|| $this->isFormFieldEnabled('background_knowledge')
					|| $this->isFormFieldEnabled('known_from')
					|| $this->isFormFieldEnabled('notes');
				break;
			case 'entered_data':
				$result = $this->isFormFieldEnabled('feuser_data')
					|| $this->isFormFieldEnabled('billing_address')
					|| $this->isFormFieldEnabled('registration_data');
				break;
			case 'all_terms':
				$result = $this->isFormFieldEnabled('terms')
					|| $this->isFormFieldEnabled('terms_2');
				break;
			case 'traveling_terms':
				// "traveling_terms" is an alias for "terms_2" which we use to
				// avoid the problem that subpart names need to be prefix-free.
				$result = $this->isFormFieldEnabled('terms_2');
				break;
			case 'billing_data':
				// "billing_data" is an alias for "billing_address" which we use
				// to prevent two subparts from having the same name.
				$result = $this->isFormFieldEnabled('billing_address');
				break;
			default:
				$result = isset($this->formFieldsToShow[$key]);
				break;
		}

		// Some fields depend on the availability of their data.
		switch ($key) {
			case 'method_of_payment':
				$result &= $this->showMethodsOfPayment();
				break;
			case 'account_number':
				// The fallthrough is intended.
			case 'bank_code':
				// The fallthrough is intended.
			case 'bank_name':
				// The fallthrough is intended.
			case 'account_owner':
				$result &= $this->seminar->hasAnyPrice();
				break;
			case 'lodgings':
				$result &= $this->hasLodgings();
				break;
			case 'foods':
				$result &= $this->hasFoods();
				break;
			case 'checkboxes':
				$result &= $this->hasCheckboxes();
				break;
			case 'terms_2':
				$result &= $this->isTerms2Enabled();
				break;
			default:
				break;
		}

		return $result;
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
	function hasBankDataFormField(array $parameters) {
		return $this->hasRegistrationFormField($parameters)
			&& $this->seminar->hasAnyPrice();
	}

	/**
	 * Gets the URL of the page that should be displayed after a user has
	 * signed up for an event, but only if the form has been submitted from
	 * stage 2 (the confirmation page).
	 *
	 * If the current FE user account is a one-time account and
	 * checkLogOutOneTimeAccountsAfterRegistration is enabled in the TS setup,
	 * the FE user will be automatically logged out.
	 *
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 *
	 * @return	string		complete URL of the FE page with a message (or null if the confirmation page has not been submitted yet)
	 *
	 * @access	public
	 */
	function getThankYouAfterRegistrationUrl(array $parameters) {
		$sendParameters = false;
		$pageId = $this->plugin->getConfValueInteger(
			'thankYouAfterRegistrationPID',
			's_registration'
		);

		if ($this->getConfValueBoolean('logOutOneTimeAccountsAfterRegistration')
				&& $GLOBALS['TSFE']->fe_user->getKey('user', 'onetimeaccount')) {
			$GLOBALS['TSFE']->fe_user->logoff();
		}

		if (
			$this->plugin->getConfValueBoolean(
				'sendParametersToThankYouAfterRegistrationPageUrl',
				's_registration'
			)
		) {
			$sendParameters = true;
		}

		return $this->createUrlForRedirection($pageId, $sendParameters);
	}

	/**
	 * Gets the URL of the page that should be displayed after a user has
	 * unregistered from an event.
	 *
	 * @return	string		complete URL of the FE page with a message (or null
	 * 						if the confirmation page has not been submitted yet)
	 *
	 * @access	public
	 */
	function getPageToShowAfterUnregistrationUrl() {
		$sendParameters = false;
		$pageId = $this->plugin->getConfValueInteger(
			'pageToShowAfterUnregistrationPID',
			's_registration'
		);

		if (
			$this->plugin->getConfValueBoolean(
				'sendParametersToPageToShowAfterUnregistrationUrl',
				's_registration'
			)
		) {
			$sendParameters = true;
		}

		return $this->createUrlForRedirection($pageId, $sendParameters);
	}

	/**
	 * Creates a URL for redirection. This is a utility function for
	 * getThankYouAfterRegistrationUrl() and getPageToShowAfterUnregistration().
	 *
	 * @param	string		the page UID
	 * @param	boolean		true if GET parameters should be added to the URL,
	 * 						otherwise false
	 *
	 * @return	string		complete URL of the FE page with a message (or null
	 * 						if the confirmation page has not been submitted yet)
	 */
	protected function createUrlForRedirection($pageId, $sendParameters = true) {
		// On freshly updated sites, the configuration value might not be set
		// yet. To avoid breaking the site, we use the event list in this case.
		if (!$pageId) {
			$pageId = $this->plugin->getConfValueInteger('listPID', 'sDEF');
		}

		$linkConfiguration = array('parameter' => $pageId);

		if ($sendParameters) {
			$linkConfiguration['additionalParams']
				= t3lib_div::implodeArrayForUrl(
					'tx_seminars_pi1',
					array('showUid' => $this->seminar->getUid()),
					'',
					false,
					true
				);
		}

		// XXX We need to do this workaround of manually encoding brackets in
		// the URL due to a bug in the TYPO3 core:
		// http://bugs.typo3.org/view.php?id=3808
		$result = preg_replace(
			array('/\[/', '/\]/'),
			array('%5B', '%5D'),
			$this->cObj->typoLink_URL($linkConfiguration)
		);

		return t3lib_div::locationHeaderUrl(
			$result
		);
	}

	/**
	 * Provides data items for the list of available places.
	 *
	 * @param	array		array that contains any pre-filled data (may be empty, but not null, unused)
	 *
	 * @return	array		items from the payment methods table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateListPaymentMethods(array $items) {
		$result = array();

		if ($this->seminar->hasPaymentMethods()) {
			$result = $this->populateList(
				$items,
				SEMINARS_TABLE_PAYMENT_METHODS,
				'uid IN ('.$this->seminar->getPaymentMethodsUids().')',
				true
			);
		}

		return $result;
	}

	/**
	 * Checks whether the methods of payment should be displayed at all,
	 * ie. whether they are enable in the setup and the current event actually
	 * has any payment methods assigned and has at least one price.
	 *
	 * @return	boolean		true if the payment methods should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function showMethodsOfPayment() {
		return $this->seminar->hasPaymentMethods()
			&& $this->seminar->hasAnyPrice()
			&& $this->hasRegistrationFormField(
				array('elementname' => 'method_of_payment')
			);
	}

	/**
	 * Gets the currently logged-in FE user's data nicely formatted as HTML so
	 * that it can be directly included on the confirmation page.
	 *
	 * The telephone number and the e-mail address will have labels in front of
	 * them.
	 *
	 * @return	string		the currently logged-in FE user's data
	 */
	public function getAllFeUserData() {
		$userData = $GLOBALS['TSFE']->fe_user->user;

		foreach (array(
			'name' => false,
			'company' => false,
			'address' => false,
			'zip' => false,
			'city' => false,
			'country' => false,
			'telephone' => true,
			'email' => true
		) as $currentKey => $hasLabel) {
			$value = htmlspecialchars($userData[$currentKey]);
			// Only show a label if we have any data following it.
			if ($hasLabel && !empty($value)) {
				$value = $this->plugin->translate('label_' . $currentKey) .
					' ' . $value;
			}
			$this->plugin->setMarker(
				'user_' . $currentKey,
				$value
			);
		}

		$rawOutput
			= $this->plugin->getSubpart('REGISTRATION_CONFIRMATION_FEUSER');

		// drops empty lines
		return preg_replace('/[\n\r]\s*<br \/>/', '', $rawOutput);
	}

	/**
	 * Gets the already entered registration data nicely formatted as HTML so
	 * that it can be directly included on the confirmation page.
	 *
	 * @return	string		the entered registration data, nicely formatted as
	 * 						HTML
	 */
	public function getRegistrationData() {
		$result = '';

		foreach (array(
			'price',
			'seats',
			'total_price',
			'method_of_payment',
			'account_number',
			'bank_code',
			'bank_name',
			'account_owner',
			'attendees_names',
			'lodgings',
			'accommodation',
			'foods',
			'food',
			'checkboxes',
			'interests',
			'expectations',
			'background_knowledge',
			'known_from',
			'notes'
		) as $currentKey) {
			$result .= $this->getFormDataItemForConfirmation($currentKey);
		}

		return $result;
	}

	/**
	 * Formats one data item from the form as HTML, including a heading.
	 * If the entered data is empty, an empty string will be returned (so the
	 * heading will only be included for non-empty data).
	 *
	 * @param	string		key of the field for which the data should be displayed
	 *
	 * @return	string		the data from the corresponding form field formatted in HTML with a heading (or an empty string if the form data is empty)
	 *
	 * @access	protected
	 */
	function getFormDataItemForConfirmation($key) {
		$result = '';

		// The "total_price" field doesn't exist as an actual renderlet and
		// so cannot be read.
		$currentFormData = ($key != 'total_price')
			? $this->oForm->oDataHandler->_getThisFormData($key) : '';

		switch ($key) {
			case 'price':
				$currentFormData = $this->getSelectedPrice();
				break;
			case 'total_price':
				$currentFormData = $this->getTotalPriceWithUnit();
				break;
			case 'method_of_payment':
				$currentFormData = $this->getSelectedPaymentMethod();
				break;
			case 'lodgings':
				$this->ensureArray($currentFormData);
				$currentFormData = $this->getCaptionsForSelectedOptions(
					$this->seminar->getLodgings(),
					$currentFormData
				);
				break;
			case 'foods':
				$this->ensureArray($currentFormData);
				$currentFormData = $this->getCaptionsForSelectedOptions(
					$this->seminar->getFoods(),
					$currentFormData
				);
				break;
			case 'checkboxes':
				$this->ensureArray($currentFormData);
				$currentFormData = $this->getCaptionsForSelectedOptions(
					$this->seminar->getCheckboxes(),
					$currentFormData
				);
				break;
			default:
				break;
		}

		if (!is_string($currentFormData)) {
			$currentFormData = (string) $currentFormData;
		}

		if ($currentFormData != '') {
			$this->plugin->setMarker(
				'registration_data_heading',
				$this->plugin->translate('label_'.$key)
			);
			$fieldContent = str_replace(
				CR,
				'<br />',
				htmlspecialchars($currentFormData)
			);
			$this->plugin->setMarker(
				'registration_data_body',
				$fieldContent
			);
			$result = $this->plugin->getSubpart(
				'REGISTRATION_CONFIRMATION_DATA'
			);
		}

		return $result;
	}

	/**
	 * Ensures that the parameter is an array. If it is no array yet, it will
	 * be changed to an empty array.
	 *
	 * @param	mixed		variable that should be ensured to be an array
	 * 						(passed by reference)
	 */
	private function ensureArray(&$data) {
		if (!is_array($data)) {
			$data = array();
		}
	}

	/**
	 * Retrieves the selected price, completely with caption (for example:
	 * "Standard price") and currency.
	 *
	 * If no price has been selected, the first available price will be used.
	 *
	 * @return	string		the selected price with caption and unit
	 *
	 * @access	protected
	 */
	function getSelectedPrice() {
		$availablePrices = $this->seminar->getAvailablePrices();

		return $availablePrices[$this->getKeyOfSelectedPrice()]['caption'];
	}

	/**
	 * Retrieves the key of the selected price.
	 *
	 * If no price has been selected, the first available price will be used.
	 *
	 * @return	string		the key of the selected price, will always be a valid key
	 *
	 * @access	protected
	 */
	function getKeyOfSelectedPrice() {
		$dataHandler =& $this->oForm->oDataHandler;

		$availablePrices = $this->seminar->getAvailablePrices();
		$selectedPrice = $dataHandler->_getThisFormData('price');

		// If no (available) price is selected, use the first price by default.
		if (!$this->seminar->isPriceAvailable($selectedPrice)) {
			$selectedPrice = key($availablePrices);
		}

		return $selectedPrice;
	}

	/**
	 * Takes the selected price and the selected number of seats and calculates
	 * the total price. The total price will be returned with the currency
	 * unit appended.
	 *
	 * @return	string		the total price calculated from the form data including the currency unit, eg. "240.00 EUR"
	 *
	 * @access	protected
	 */
	function getTotalPriceWithUnit() {
		$result = '';

		$dataHandler =& $this->oForm->oDataHandler;
		$seats = intval($dataHandler->_getThisFormData('seats'));

		// Only show the total price if the seats selector is displayed
		// (otherwise the total price will be same as the price anyway).
		if ($seats > 0) {
			// Build the total price for this registration and add it to the form
			// data to show it on the confirmation page.
			// This value will not be saved to the database from here. It will be
			// calculated again when creating the registration object.
			// It will not be added if no total price can be calculated (e.g.
			// total price = 0.00)
			$availablePrices = $this->seminar->getAvailablePrices();
			$selectedPrice = $this->getKeyOfSelectedPrice();

			if ($availablePrices[$selectedPrice]['amount'] != '0.00') {
				$totalPrice = $this->seminar->formatPrice(
					$seats * $availablePrices[$selectedPrice]['amount']
				);
				$currency = $this->registrationManager->getConfValueString(
					'currency'
				);
				$result = $totalPrice.' '.$currency;
			}
		}

		return $result;
	}

	/**
	 * Gets the caption of the selected payment method. If no valid payment
	 * method has been selected, this function returns an empty string.
	 *
	 * @return	string		the caption of the selected payment method or an empty string if no valid payment method has been selected
	 *
	 * @access	protected
	 */
	function getSelectedPaymentMethod() {
		$result = '';

		$dataHandler =& $this->oForm->oDataHandler;

		$availablePaymentMethods = $this->populateListPaymentMethods(
			array()
		);

		if (isset($availablePaymentMethods[
			$dataHandler->_getThisFormData('method_of_payment')
		])) {
			$result = $availablePaymentMethods
				[$dataHandler->_getThisFormData('method_of_payment')]['caption'];
		}

		// We use strip_tags to remove any trailing <br /> tags.
		return strip_tags($result);
	}

	/**
	 * Takes the selected options for a list of options and displays it
	 * nicely using their captions, separated by a carriage return (ASCII 13).
	 *
	 * @param	array		all available options for this form element as a nested array, the outer array having the UIDs of the options as keys, the inner array having the keys "caption" (for the visible captions) and "value" (the UID again), may be empty, must not be null
	 * @param	array		the selected options with the array values being the UIDs of the corresponding options, may be empty or even null
	 *
	 * @return	string		the captions of the selected options, separated by CR
	 */
	function getCaptionsForSelectedOptions(
		array $availableOptions, array $selectedOptions
	) {
		$result = '';

		if (!empty($selectedOptions)) {
			$captions = array();

			foreach ($selectedOptions as $currentSelection) {
				if (isset($availableOptions[$currentSelection])) {
					$captions[]	= $availableOptions[$currentSelection]['caption'];
				}
				$result = implode(CR, $captions);
			}
		}

		return $result;
	}

	/**
	 * Gets the already entered billing address nicely formatted as HTML so
	 * that it can be directly included on the confirmation page.
	 *
	 * @return	string		the already entered registration data, nicely formatted as HTML
	 *
	 * @access	public
	 */
	function getBillingAddress() {
		$result = '';

		foreach ($this->fieldsInBillingAddress as $currentKey => $hasLabel) {
			$currentFormData = $this->oForm->oDataHandler->_getThisFormData(
				$currentKey
			);
			if ($currentFormData != '') {
				// If the gender field is hidden, it would have an empty value,
				// so we wouldn't be here. So let's convert the "gender" index
				// into a readable string.
				if ($currentKey == 'gender') {
					$currentFormData =
						$this->translate('label_gender.I.'.intval($currentFormData));
				}
				$processedFormData = str_replace(
					CR,
					'<br />',
					htmlspecialchars($currentFormData)
				);
				if ($hasLabel) {
					$processedFormData
						= $this->plugin->translate('label_'.$currentKey)
							.' '.$processedFormData;
				}

				$result .= $processedFormData.'<br />';
			}
		}

		$this->plugin->setMarker('registration_billing_address', $result);

		return $this->plugin->getSubpart('REGISTRATION_CONFIRMATION_BILLING');
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
	function hasAttendeesNames($attendeesNames, tx_ameosformidable $form) {
		$dataHandler = $form->oDataHandler;
		$seats = (intval($dataHandler->_getThisFormData('seats')) > 0) ?
			intval($dataHandler->_getThisFormData('seats')) : 1;

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
	function hasBankData($bankData, tx_ameosformidable $form) {
		$result = true;

		if (empty($bankData)) {
			$bankTransferUid = $this->plugin->getConfValueInteger('bankTransferUID');

			$dataHandler = $form->oDataHandler;
			$paymentMethod
				= intval($dataHandler->_getThisFormData('method_of_payment'));

			if ($bankTransferUid && ($paymentMethod == $bankTransferUid)) {
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Returns a data item of the currently logged-in FE user or, if that data
	 * has additionally been stored in the FE user session (as billing address),
	 * the data from the session.
	 *
	 * This function may only be called when a FE user is logged in.
	 *
	 * The caller needs to take care of htmlspecialcharing the data.
	 *
	 * @param	mixed		(unused)
	 * @param	array		contents of the "params" XML child of the userobj node (needs to contain an element with the key "key")
	 *
	 * @return	string		the contents of the element
	 *
	 * @access	public
	 */
	function getFeUserData($unused, array $params) {
		$result = $this->retrieveDataFromSession(null, $params);

		if (empty($result)) {
			$key = $params['key'];
			$feUserData = $GLOBALS['TSFE']->fe_user->user;
			$result = $feUserData[$key];

			// If the country is empty, try the static info country instead.
			if (empty($result) && ($key == 'country')) {
				$static_info_country = $feUserData['static_info_country'];
				if (!empty($static_info_country)) {
					$this->initStaticInfo();
					$result = $this->staticInfo->getStaticInfoName(
						'COUNTRIES',
						$static_info_country
					);
				}
			}
		}

		return $result;
	}

	/**
	 * Provides a localized list of country names from static_tables.
	 *
	 * @return	array		a list of localized country names from static_tables as an array with the keys "caption" (for the title) and "value" (in this case, the same as the caption)
	 *
	 * @access	public
	 */
	function populateListCountries() {
		$this->initStaticInfo();
		$allCountries = $this->staticInfo->initCountries();

		$result = array();
		// Add an empty item at the top so we won't have Afghanistan (the first
		// entry) pre-selected for empty values.
		$result[] = array(
			'caption' => '&nbsp;',
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
	 * @return	array		items from the checkboxes table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateCheckboxes() {
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
	 * @return	array		items from the lodgings table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateLodgings() {
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
	 *
	 * @return	boolean		true if at least one item is selected or no lodging options can be selected
	 *
	 * @access	public
	 */
	function isLodgingSelected($selection) {
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
	 * @return	array		items from the foods table as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populateFoods() {
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
	 *
	 * @return	boolean		true if at least one item is selected or no food options can be selected
	 *
	 * @access	public
	 */
	function isFoodSelected($selection) {
		return !empty($selection) || !$this->hasFoods();
	}

	/**
	 * Provides data items for the prices for this event.
	 *
	 * @return	array		available prices as an array with the keys "caption" (for the title) and "value" (for the uid)
	 *
	 * @access	public
	 */
	function populatePrices() {
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

	/**
	 * Returns the UID of the preselected payment method.
	 *
	 * This will be:
	 * a) the same payment method as previously selected (within the current
	 * session) if that method is available for the current event
	 * b) if only one payment method is available, that payment method
	 * c) 0 in all other cases
	 *
	 * @return	integer		the UID of the preselected payment method or 0 if should will be preselected
	 *
	 * @access	public
	 */
	function getPreselectedPaymentMethod() {
		$result = 0;

		$availablePaymentMethods = $this->populateListPaymentMethods(array());
		if (count($availablePaymentMethods) == 1) {
			$result = key($availablePaymentMethods);
		} else {
			$paymentMethodFromSession = $this->retrieveSavedMethodOfPayment();
			if (isset($availablePaymentMethods[$paymentMethodFromSession])) {
				$result = $paymentMethodFromSession;
			}
		}

		return $result;
	}

	/**
	 * Saves the following data to the FE user session:
	 * - payment method
	 * - account number
	 * - bank code
	 * - bank name
	 * - account_owner
	 * - gender
	 * - name
	 * - address
	 * - zip
	 * - city
	 * - country
	 * - telephone
	 * - email
	 *
	 * @param	array		the form data (may be empty)
	 *
	 * @access	private
	 */
	function saveDataToSession(array $parameters) {
		if (!empty($parameters)) {
			$parametersToSave = array(
				'method_of_payment',
				'account_number',
				'bank_code',
				'bank_name',
				'account_owner',
				'gender',
				'name',
				'address',
				'zip',
				'city',
				'country',
				'telephone',
				'email'
			);

			foreach ($parametersToSave as $currentKey) {
				if (isset($parameters[$currentKey])) {
					$GLOBALS['TSFE']->fe_user->setKey(
						'user',
						$this->prefixId.'_'.$currentKey,
						$parameters[$currentKey]
					);
				}
			}

			$GLOBALS['TSFE']->fe_user->storeSessionData();
		}
	}

	/**
	 * Retrieves the saved payment method from the FE user session.
	 *
	 * @return	integer		the UID of the payment method that has been saved in the FE user session or 0 if there is none
	 *
	 * @access	private
	 */
	function retrieveSavedMethodOfPayment() {
		return intval(
			$this->retrieveDataFromSession(
				null,
				array('key' => 'method_of_payment')
			)
		);
	}

	/**
	 * Retrieves the data for a given key from the FE user session. Returns an
	 * empty string if no data for that key is stored.
	 *
	 * @param	mixed		(unused)
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 *
	 * @return	string		the data stored in the FE user session under the given key (might be empty)
	 *
	 * @access	public
	 */
	function retrieveDataFromSession($unused, array $parameters) {
		$key = $parameters['key'];

		return $GLOBALS['TSFE']->fe_user->getKey(
			'user',
			$this->prefixId.'_'.$key
		);
	}

	/**
	 * Gets the prefill value for the account owner: If it is provided, the
	 * account owner from a previous registration in the same FE user session,
	 * or the FE user's name.
	 *
	 * @return	string		a name to prefill the account owner
	 *
	 * @access	public
	 */
	function prefillAccountOwner() {
		$result = $this->retrieveDataFromSession(
			null,
			array('key' => 'account_owner')
		);

		if (empty($result)) {
			$result = $this->getFeUserData(
				null,
				array('key' => 'name')
			);
		}

		return $result;
	}

	/**
	 * Creates and initializes $this->staticInfo (if that hasn't been done yet).
	 *
	 * @access	private
	 */
	function initStaticInfo() {
		if (!$this->staticInfo) {
			$this->staticInfo =& t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$this->staticInfo->init();
		}
	}

	/**
	 * Hides form fields that are either disabled via TS setup or that have
	 * nothing to select (e.g. if there are no payment methods) from the
	 * templating process.
	 *
	 * @access	protected
	 */
	function hideUnusedFormFields() {
		static $availableFormFields = array(
			'step_counter',
			'payment',
			'price',
			'method_of_payment',
			'banking_data',
			'account_number',
			'bank_code',
			'bank_name',
			'account_owner',
			'billing_address',
			'billing_data',
			'gender',
			'name',
			'address',
			'zip',
			'city',
			'country',
			'telephone',
			'email',
			'additional_information',
			'interests',
			'expectations',
			'background_knowledge',
			'lodging_and_food',
			'accommodation',
			'food',
			'known_from',
			'more_seats',
			'seats',
			'attendees_names',
			'kids',
			'lodgings',
			'foods',
			'checkboxes',
			'notes',
			'entered_data',
			'feuser_data',
			'registration_data',
			'all_terms',
			'terms',
			'terms_2',
			'traveling_terms'
		);

		$formFieldsToHide = array();

		foreach ($availableFormFields as $key) {
			if (!$this->isFormFieldEnabled($key)) {
				$formFieldsToHide[$key] = $key;
			}
		}

		$numberOfClicks = $this->plugin->getConfValueInteger(
			'numberOfClicksForRegistration',
			's_registration'
		);

		// If we first visit the registration form, the value of
		// $this->currentPageNumber is 0.
		// If we had an error in our form input and we were send back to the
		// registration form, $this->currentPageNumber is 2.
		if (($this->currentPageNumber == 0) || ($this->currentPageNumber == 2)) {
			switch ($numberOfClicks) {
				case 2:
					$formFieldsToHide['button_continue'] = 'button_continue';
					break;
				case 3:
					// The fall-through is intended.
				default:
					$formFieldsToHide['button_submit'] = 'button_submit';
					break;
			}
		}

		$this->hideSubparts(
			implode(',', $formFieldsToHide),
			'registration_wrapper'
		);
	}

	/**
	 * Provides a string "Registration form: step x of y" for the current page.
	 * The number of the first and last page can be configured via TS setup.
	 *
	 * @return	string		a localized string displaying the number of the current and the last page
	 *
	 * @access	public
	 */
	function getStepCounter() {
		$currentPageNumberForDisplay = $this->plugin->getConfValueInteger(
			'numberOfFirstRegistrationPage'
		) + $this->currentPageNumber;
		$lastPageNumberForDisplay = $this->plugin->getConfValueInteger(
			'numberOfLastRegistrationPage'
		);

		// Decreases $lastPageNumberForDisplay by one if we only have 2 clicks
		// to registration.
		$numberOfClicks = $this->plugin->getConfValueInteger(
			'numberOfClicksForRegistration',
			's_registration'
		);

		if ($numberOfClicks == 2) {
			$lastPageNumberForDisplay--;
		}

		return sprintf(
			$this->plugin->translate('label_step_counter'),
			$currentPageNumberForDisplay, $lastPageNumberForDisplay
		);
	}

	/**
	 * Processes the registration that should be removed.
	 */
	public function processUnregistration() {
		$this->registrationManager->removeRegistration(
			$this->plugin->getRegistration()->getUid(),
			$this->plugin
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_registration_editor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_registration_editor.php']);
}
?>