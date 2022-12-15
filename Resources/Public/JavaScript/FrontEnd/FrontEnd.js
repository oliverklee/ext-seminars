/*
 * This file provides some JavaScript functions for the seminars front-end
 * editor and the registration form.
 */

;((root, exports, $) => {
  'use strict'

  class Seminars {
    /**
     * Classes that will show an element (with `display: block;`).
     *
     * The first class comes from Bootstrap, while the other is our own
     * (so that this feature works both with the Bootstrap CSS or the seminars CSS).
     */
    static visibilityClasses = ['d-block', 'tx-seminars-display-block']

    /**
     * Classes that will hide an element (with `display: none;`).
     *
     * The first class comes from Bootstrap, while the other is our own
     * (so that this feature works both with the Bootstrap CSS or the seminars CSS).
     */
    static invisibilityClasses = ['d-none', 'tx-seminars-display-none']

    elements = {}

    constructor() {
      document.addEventListener('readystatechange', () => {
        this.initializeSearchWidget();
        this.initializeLegacyRegistrationForm();
        this.convertActionLinks();
        this.initializeRegistrationForm();
      });
    }

    /**
     * Collects the names from the first/last name field pairs and compiles/inserts
     * them into the human-readable "additional attendees" field and the machine-readable
     * "structured attendees" field.
     */
    compileNames() {
      var $nameFieldsContainer = $('#tx_seminars_pi1_registration_editor_separate_names');
      if ($nameFieldsContainer.length === 0) {
        return;
      }

      var humanReadableField = $('#tx_seminars_pi1_registration_editor__attendees_names')[0];
      var machineReadableField = $('#tx_seminars_pi1_registration_editor__structured_attendees_names')[0];

      var separateNamesElement = $('#tx_seminars_pi1_registration_editor_separate_names');

      var firstNames = separateNamesElement.find('.tx_seminars_pi1_registration_editor_first_name');
      var lastNames = separateNamesElement.find('.tx_seminars_pi1_registration_editor_last_name');
      var positions = separateNamesElement.find('.tx_seminars_pi1_registration_editor_position');
      var eMailAddresses = separateNamesElement.find('.tx_seminars_pi1_registration_editor_attendee_email');

      var humanReadableNames = '';
      var machineReadableNames = [];

      var numberOfLines = firstNames.length;

      for (var i = 0; i < numberOfLines; i++) {
        var firstName = $.trim(firstNames[i].value);
        var lastName = $.trim(lastNames[i].value);

        if (firstName === '' && lastName === '') {
          continue;
        }

        var position = '';
        if (i < positions.length) {
          position = $.trim(positions[i].value);
        }

        var eMailAddress = '';
        if (i < eMailAddresses.length) {
          eMailAddress = $.trim(eMailAddresses[i].value);
        }

        var fullName = $.trim(firstName + ' ' + lastName);
        if (humanReadableNames !== '') {
          humanReadableNames += "\r\n";
        }
        humanReadableNames += fullName;

        if (position !== '') {
          humanReadableNames += ', ' + position;
        }
        if (eMailAddress !== '') {
          humanReadableNames += ', ' + eMailAddress;
        }

        machineReadableNames[i] = [firstName, lastName, position, eMailAddress];
      }

      humanReadableField.value = humanReadableNames;
      machineReadableField.value = JSON.stringify(machineReadableNames);
    }

    /**
     * Restores the separate name fields from the hidden field with the names
     * in a JSON-encoded array.
     */
    restoreSeparateNameFields() {
      var machineReadableField = $('#tx_seminars_pi1_registration_editor__structured_attendees_names')[0];

      if (!machineReadableField || machineReadableField.value === '') {
        return;
      }

      var separateNamesElement = $('#tx_seminars_pi1_registration_editor_separate_names');
      var firstNames = separateNamesElement.find('.tx_seminars_pi1_registration_editor_first_name');
      var lastNames = separateNamesElement.find('.tx_seminars_pi1_registration_editor_last_name');
      var positions = separateNamesElement.find('.tx_seminars_pi1_registration_editor_position');
      var eMailAddresses = separateNamesElement.find('.tx_seminars_pi1_registration_editor_attendee_email');

      if (firstNames.length !== lastNames.length) {
        return;
      }

      var allNames = JSON.parse(machineReadableField.value);
      var numberOfNames = Math.min(firstNames.length, allNames.length);

      for (var i = 0; i < numberOfNames; i++) {
        firstNames[i].value = allNames[i][0];
        lastNames[i].value = allNames[i][1];
        if (positions[i]) {
          positions[i].value = allNames[i][2];
        }
        if (eMailAddresses[i]) {
          eMailAddresses[i].value = allNames[i][3];
        }
      }
    }

    /**
     * Adds or drops name fields to match the number of selected seats.
     */
    fixNameFieldsNumber() {
      var neededNameLines = this.getNumberOfNeededNameFields();
      var nameLines = $('#tx_seminars_pi1_registration_editor_separate_names .tx_seminars_pi1_registration_editor_name_line');

      if (nameLines.length < neededNameLines) {
        var nameLineTemplate = $('#tx_seminars_pi1_registration_editor_name_template .tx_seminars_pi1_registration_editor_name_line')[0];
        if (!nameLineTemplate) {
          return;
        }

        var nameLinesContainer = $('#tx_seminars_pi1_registration_editor_separate_names');

        for (var i = nameLines.length; i < neededNameLines; i++) {
          nameLinesContainer.append(nameLineTemplate.cloneNode(true));
        }
      } else if (nameLines.length > neededNameLines) {
        for (var j = nameLines.length; j > neededNameLines; j--) {
          $(nameLines[j - 1]).remove();
        }
      }
    }

    /**
     * Gets the number of needed name fields.
     *
     * @return {Number} the number of needed name fields, will be >= 0
     */
    getNumberOfNeededNameFields() {
      var seatsElements = $('#tx_seminars_pi1_registration_editor__seats');
      if (seatsElements.length === 0) {
        return 0;
      }

      var seats = parseInt(seatsElements[0].value);

      var myselfSelector = $('#tx_seminars_pi1_registration_editor__registered_themselves');
      var selfSeat;
      if (myselfSelector.length > 0) {
        selfSeat = parseInt(myselfSelector.attr('value'));
      } else {
        var $defaultValue = $('#tx-seminars-pi1-themselves-default');
        if ($defaultValue.length > 0) {
          selfSeat = parseInt($defaultValue.data('value'));
        } else {
          selfSeat = 1;
        }
      }

      return seats - selfSeat;
    }

    /**
     * Clears the selection of the search widget.
     */
    clearSearchWidgetFields() {
      var prefix = 'tx_seminars_pi1';
      var textElements = ['sword', 'search_age', 'price_from', 'price_to'];
      for (var i = 0; i < textElements.length; i++) {
        var textElement = document.getElementById(prefix + '_' + textElements[i]);
        if (textElement) {
          textElement.value = null;
        }
      }

      var suffixes = ['from_day', 'from_month', 'from_year', 'to_day', 'to_month',
        'to_year', 'event_type', 'language', 'country', 'city', 'place', 'date',
        'organizer', 'categories'
      ];

      for (var j = 0; j < suffixes.length; j++) {
        var suffix = suffixes[j];
        var element = document.getElementById(prefix + '-' + suffix);
        if (element) {
          for (var k = 0; k < element.options.length; k++) {
            element.options[k].selected = false;
          }
        }
      }
    }

    /**
     * Converts the links that have a data-method="post" to JavaScript-powered on-the-fly forms.
     */
    convertActionLinks() {
      $('.tx-seminars-pi1 a[data-method]').click(this.executeLinkAction);
    }

    /**
     * Executes the action on a link.
     *
     * @param {MouseEvent} event
     */
    executeLinkAction(event) {
      var linkElement = event.target;
      var linkHref = linkElement.getAttribute('href');

      this.disableAllActionLinks();

      var formElement = document.createElement("form");
      formElement.style.display = 'none';
      formElement.setAttribute('method', 'post');
      formElement.setAttribute('action', linkHref);

      for (var j = 0; j < linkElement.attributes.length; j++) {
        var attribute = linkElement.attributes[j];
        var name = attribute.name;
        if (/^data-post-/.test(name)) {
          var dataParts = name.split('-');
          var inputElement = document.createElement('input');
          inputElement.setAttribute('type', 'hidden');
          inputElement.setAttribute('name', dataParts[2] + '[' + dataParts[3] + ']');
          inputElement.setAttribute('value', attribute.value);
          formElement.appendChild(inputElement);
        }
      }

      linkElement.appendChild(formElement);
      formElement.submit();

      return false;
    }

    /**
     * Disables all action links (so that they cannot be clicked again once an action is being processed).
     */
    disableAllActionLinks() {
      var linkElements = document.querySelectorAll('a[data-method]');
      for (var i = 0; i < linkElements.length; i++) {
        linkElements[i].onclick = () => {
          return false;
        };
      }
    }

    /**
     * Prevents registration form submit event to be called twice.
     */
    preventMultipleFormSubmit() {
      var submitForm = document.getElementById('tx_seminars_pi1_registration_editor');
      var submitButton = document.getElementById('tx_seminars_pi1_registration_editor__button_submit');
      submitForm.addEventListener('submit', (event) => {
        if (submitButton.hasAttribute('disabled')) {
          event.preventDefault();
        }
      });
    }

    /**
     * Initializes the search widget.
     */
    initializeSearchWidget() {
      if ($('.tx-seminars-pi1-selectorwidget').length === 0) {
        return;
      }

      $('#tx-seminars-pi1-clear-search-widget').click(() => {
        this.clearSearchWidgetFields();
      });
    }

    /**
     * This method updates the UI if anything corresponding the number of seats has changed.
     */
    updateAttendees() {
      this.fixNameFieldsNumber();
      this.compileNames();
    }

    initializeLegacyRegistrationForm() {
      var registrationForm = $('#tx-seminars-pi1-registration-form');
      if (registrationForm.length === 0) {
        return;
      }

      registrationForm.find('#tx_seminars_pi1_registration_editor_separate_names').on('blur', 'input', this.compileNames);
      registrationForm.find('#tx_seminars_pi1_registration_editor__seats').change(this.updateAttendees);
      registrationForm.find('#tx_seminars_pi1_registration_editor__registered_themselves_checkbox').click(this.updateAttendees);

      this.fixNameFieldsNumber();
      this.restoreSeparateNameFields();
      this.compileNames();
      this.preventMultipleFormSubmit();
    }

    findRegistrationFormElements() {
      const selectors = {
        registrationForm: 'form[data-behavior="tx-seminars-registration-form"]',
        billingAddressCheckbox: 'input[data-behavior="tx-seminars-billing-address-toggle"]',
        billingAddressFields: '[data-behavior="tx-seminars-billing-address-fields"]',
        seats: '[data-behavior="tx-seminars-seats"]',
        registeredThemselves: '[data-behavior="tx-seminars-registered-themselves"]',
        separateAttendeeNames: '[data-behavior="tx-seminars-separate-attendee-names"]',
        attendeeTemplate: '[data-behavior="tx-seminars-attendee-template"]',
        humanReadableAttendeeNames: '[data-behavior="tx-seminars-human-readable-attendee-names"]',
        machineReadableAttendeeNames: '[data-behavior="tx-seminars-machine-readable-attendee-names"]',
      }

      for (const [key, selector] of Object.entries(selectors)) {
        this.elements[key] = document.querySelector(selector);
      }
    }

    existsRegistrationForm() {
      return this.elements.registrationForm instanceof Element;
    }

    initializeRegistrationForm() {
      this.findRegistrationFormElements();
      if (!this.existsRegistrationForm()) {
        return;
      }

      this.updateBillingAddressVisibility();
      this.addBillingAddressCheckboxListener();

      this.showOrHideAttendeeNames();
      this.restoreAttendeeNames();
      this.addSeatsListener();
    }

    addBillingAddressCheckboxListener() {
      if (!(this.elements.billingAddressCheckbox instanceof Element)) {
        return;
      }

      this.elements.billingAddressCheckbox.addEventListener('change', this.updateBillingAddressVisibility.bind(this));
    }

    addSeatsListener() {
      if (this.elements.seats instanceof Element) {
        this.elements.seats.addEventListener('change', this.showOrHideAttendeeNames.bind(this));
      }
      if (this.elements.registeredThemselves instanceof Element) {
        this.elements.registeredThemselves
          .addEventListener('change', this.showOrHideAttendeeNames.bind(this));
      }
    }

    updateBillingAddressVisibility() {
      if (!(this.elements.billingAddressCheckbox instanceof Element)
        || !(this.elements.billingAddressFields instanceof Element)
      ) {
        return;
      }

      const shouldShowBillingAddress = !!this.elements.billingAddressCheckbox.checked;
      if (shouldShowBillingAddress) {
        this.showElement(this.elements.billingAddressFields);
      } else {
        this.hideElement(this.elements.billingAddressFields);
      }
    }

    /**
     * Shows/hides the attendee fields depending on the number of seats.
     */
    showOrHideAttendeeNames() {
      if (!(this.elements.separateAttendeeNames instanceof Element)) {
        return;
      }

      const otherSeats = Math.max(this.numberOfRequiredAdditionalAttendees(), 0);
      if (otherSeats > 0) {
        this.showElement(this.elements.separateAttendeeNames);
      } else {
        this.hideElement(this.elements.separateAttendeeNames);
      }
      if (!(this.elements.attendeeTemplate instanceof Element)) {
        return;
      }

      let currentlyVisibleAttendeeFieldsCount = this.elements.separateAttendeeNames.children.length;
      while (currentlyVisibleAttendeeFieldsCount !== otherSeats) {
        if (currentlyVisibleAttendeeFieldsCount < otherSeats) {
          this.addAttendeeLine();
          currentlyVisibleAttendeeFieldsCount++;
        } else {
          this.removeLastAttendeeLine();
          currentlyVisibleAttendeeFieldsCount--;
        }
      }
    }

    addAttendeeLine() {
      const newAttendeeLine = this.elements.attendeeTemplate.cloneNode(true);
      const newAttendeeLineNumber = (this.elements.separateAttendeeNames.children.length + 1).toString();

      for (const inputElement of newAttendeeLine.querySelectorAll('input')) {
        inputElement.setAttribute('id', inputElement.getAttribute('id').replace('xxx', newAttendeeLineNumber));
        inputElement.setAttribute('required', 'required');
        inputElement.addEventListener('change', this.compileAttendeeNames.bind(this));
      }
      for (const labelElement of newAttendeeLine.querySelectorAll('label')) {
        labelElement.setAttribute('for', labelElement.getAttribute('for').replace('xxx', newAttendeeLineNumber));
      }

      this.showElement(newAttendeeLine);
      this.elements.separateAttendeeNames.appendChild(newAttendeeLine);
    }

    /**
     * @return {NodeList}
     */
    getAttendeeLines() {
      return this.elements.separateAttendeeNames.querySelectorAll('li');
    }

    removeLastAttendeeLine() {
      const lines = this.getAttendeeLines();
      const lastLine = lines[lines.length - 1];
      for (const inputElement of lastLine.querySelectorAll('input')) {
        inputElement.removeEventListener('change', this.compileAttendeeNames);
      }

      lastLine.remove();

      this.compileAttendeeNames();
    }

    /**
     * @return {number}
     */
    numberOfRequiredAdditionalAttendees() {
      let seats = 1;
      if (this.elements.seats instanceof Element) {
        seats = parseInt(this.elements.seats.value);
      }

      let registeredThemselves = true;
      if (this.elements.registeredThemselves instanceof Element) {
        if (this.elements.registeredThemselves.type === 'checkbox') {
          registeredThemselves = this.elements.registeredThemselves.checked === true;
        } else if (this.elements.registeredThemselves.type === 'hidden') {
          registeredThemselves = this.elements.registeredThemselves.value === '1';
        }
      }
      return seats - (registeredThemselves ? 1 : 0);
    }

    /**
     * Takes the names (and potentially email addresses) of the additional attendees from the corresponding separate
     * input fields and compiles them both human-readable into the `attendeesNames` input field and machine-readable
     * into the `jsonEncodedAdditionAttendees` input field.
     */
    compileAttendeeNames() {
      if (!(this.elements.separateAttendeeNames instanceof Element)) {
        return;
      }

      let humanReadableAttendeeNames = [];
      let machineReadableAttendeeNames = [];
      for (const attendeeLine of this.getAttendeeLines()) {
        const nameInput = attendeeLine.querySelector('input[name="attendeeName"]');
        const emailInput = attendeeLine.querySelector('input[name="attendeeEmail"]');
        const name = (nameInput instanceof Element) ? nameInput.value : '';
        const email = (emailInput instanceof Element) ? emailInput.value : '';
        humanReadableAttendeeNames.push((name + ' ' + email).trim());
        machineReadableAttendeeNames.push({name: name, email: email});
      }

      const hasHumanReadableAttendeeNames = this.elements.humanReadableAttendeeNames instanceof Element;
      if (hasHumanReadableAttendeeNames) {
        this.elements.humanReadableAttendeeNames.value = humanReadableAttendeeNames.join("\n").trim();
      }
      const hasMachineReadableAttendeeNames = this.elements.machineReadableAttendeeNames instanceof Element;
      if (hasMachineReadableAttendeeNames) {
        this.elements.machineReadableAttendeeNames.value = JSON.stringify(machineReadableAttendeeNames);
      }
    }

    /**
     * Takes the machine-readable attendee names from the `jsonEncodedAdditionAttendees` input field and fills the
     * corresponding separate input fields with the names and email addresses.
     */
    restoreAttendeeNames() {
      const hasMachineReadableAttendeeNames = this.elements.machineReadableAttendeeNames instanceof Element
        && this.elements.machineReadableAttendeeNames.value !== '';
      if (!hasMachineReadableAttendeeNames) {
        return;
      }

      const attendeeNames = JSON.parse(this.elements.machineReadableAttendeeNames.value);
      if (!Array.isArray(attendeeNames)) {
        return;
      }

      let attendeeIndex = 0;
      for (const attendeeLine of this.getAttendeeLines()) {
        if (typeof (attendeeNames[attendeeIndex]) === 'object') {
          const nameInput = attendeeLine.querySelector('input[name="attendeeName"]');
          if (nameInput instanceof Element && typeof (attendeeNames[attendeeIndex].name) === 'string') {
            nameInput.value = attendeeNames[attendeeIndex].name;
          }
          const emailInput = attendeeLine.querySelector('input[name="attendeeEmail"]');
          if (emailInput instanceof Element && typeof (attendeeNames[attendeeIndex].email) === 'string') {
            emailInput.value = attendeeNames[attendeeIndex].email;
          }
        }

        attendeeIndex++;
      }
    }

    showElement(element) {
      if (!(element instanceof Element)) {
        return;
      }

      Seminars.visibilityClasses.forEach((className) => {
        element.classList.add(className);
      });
      Seminars.invisibilityClasses.forEach((className) => {
        element.classList.remove(className);
      });
    }

    hideElement(element) {
      Seminars.invisibilityClasses.forEach((className) => {
        element.classList.add(className);
      });
      Seminars.visibilityClasses.forEach((className) => {
        element.classList.remove(className);
      });
    }
  }

  exports.seminars = new Seminars();
})(
  typeof self !== 'undefined' ? self : this,
  self.TYPO3 = self.TYPO3 || {},
  self.jQuery || null,
)
