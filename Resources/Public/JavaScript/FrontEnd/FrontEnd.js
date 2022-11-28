/*
 * This file provides some JavaScript functions for the seminars front-end
 * editor and the registration form.
 */

var TYPO3 = TYPO3 || {};
TYPO3.seminars = {};
TYPO3.seminars.elements = {};

/**
 * Classes that will show an element (with `display: block;`).
 *
 * The first class comes from Bootstrap, while the other is our own
 * (so that this feature works both with the Bootstrap CSS or the seminars CSS).
 */
TYPO3.seminars.visibilityClasses = ['d-block', 'tx-seminars-display-block'];

/**
 * Classes that will hide an element (with `display: none;`).
 *
 * The first class comes from Bootstrap, while the other is our own
 * (so that this feature works both with the Bootstrap CSS or the seminars CSS).
 */
TYPO3.seminars.invisibilityClasses = ['d-none', 'tx-seminars-display-none'];

/**
 * Marks the current attachment as deleted if the confirm becomes submitted.
 *
 * @param {String} listItemId
 *        ID of the list item with the attachment to delete, must not be empty
 * @param {String} confirmMessage
 *        localized confirm message for whether really to mark an attachment for
 *        deletion
 */
TYPO3.seminars.markAttachmentAsDeleted = function(listItemId, confirmMessage) {
  var listItem = document.getElementById(listItemId);
  var deleteButton = document.getElementById(listItemId + '_button');

  if (confirm(confirmMessage)) {
    document.getElementById('tx_seminars_pi1_seminars_delete_attached_files').value += ',' + listItem.firstChild.nodeValue;
    listItem.setAttribute('class', 'deleted');
    deleteButton.disabled = true;
  }
};

/**
 * Collects the names from the first/last name field pairs and compiles/inserts
 * them into the human-readable "additional attendees" field and the machine-readable
 * "structured attendees" field.
 */
TYPO3.seminars.compileNames = function() {
  var $nameFieldsContainer = jQuery('#tx_seminars_pi1_registration_editor_separate_names');
  if ($nameFieldsContainer.length === 0) {
    return;
  }

  var humanReadableField = jQuery('#tx_seminars_pi1_registration_editor__attendees_names')[0];
  var machineReadableField = jQuery('#tx_seminars_pi1_registration_editor__structured_attendees_names')[0];

  var separateNamesElement = jQuery('#tx_seminars_pi1_registration_editor_separate_names');

  var firstNames = separateNamesElement.find('.tx_seminars_pi1_registration_editor_first_name');
  var lastNames = separateNamesElement.find('.tx_seminars_pi1_registration_editor_last_name');
  var positions = separateNamesElement.find('.tx_seminars_pi1_registration_editor_position');
  var eMailAddresses = separateNamesElement.find('.tx_seminars_pi1_registration_editor_attendee_email');

  var humanReadableNames = '';
  var machineReadableNames = [];

  var numberOfLines = firstNames.length;

  for (var i = 0; i < numberOfLines; i++) {
    var firstName = jQuery.trim(firstNames[i].value);
    var lastName = jQuery.trim(lastNames[i].value);

    if (firstName === '' && lastName === '') {
      continue;
    }

    var position = '';
    if (i < positions.length) {
      position = jQuery.trim(positions[i].value);
    }

    var eMailAddress = '';
    if (i < eMailAddresses.length) {
      eMailAddress = jQuery.trim(eMailAddresses[i].value);
    }

    var fullName = jQuery.trim(firstName + ' ' + lastName);
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
};

/**
 * Restores the separate name fields from the hidden field with the names
 * in a JSON-encoded array.
 */
TYPO3.seminars.restoreSeparateNameFields = function() {
  var machineReadableField = jQuery('#tx_seminars_pi1_registration_editor__structured_attendees_names')[0];

  if (!machineReadableField || machineReadableField.value === '') {
    return;
  }

  var separateNamesElement = jQuery('#tx_seminars_pi1_registration_editor_separate_names');
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
};

/**
 * Adds or drops name fields to match the number of selected seats.
 */
TYPO3.seminars.fixNameFieldsNumber = function() {
  var neededNameLines = TYPO3.seminars.getNumberOfNeededNameFields();
  var nameLines = jQuery('#tx_seminars_pi1_registration_editor_separate_names .tx_seminars_pi1_registration_editor_name_line');

  if (nameLines.length < neededNameLines) {
    var nameLineTemplate = jQuery('#tx_seminars_pi1_registration_editor_name_template .tx_seminars_pi1_registration_editor_name_line')[0];
    if (!nameLineTemplate) {
      return;
    }

    var nameLinesContainer = jQuery('#tx_seminars_pi1_registration_editor_separate_names');

    for (var i = nameLines.length; i < neededNameLines; i++) {
      nameLinesContainer.append(nameLineTemplate.cloneNode(true));
    }
  } else if (nameLines.length > neededNameLines) {
    for (var j = nameLines.length; j > neededNameLines; j--) {
      $(nameLines[j - 1]).remove();
    }
  }
};

/**
 * Gets the number of needed name fields.
 *
 * @return {Number} the number of needed name fields, will be >= 0
 */
TYPO3.seminars.getNumberOfNeededNameFields = function() {
  var seatsElements = jQuery('#tx_seminars_pi1_registration_editor__seats');
  if (seatsElements.length === 0) {
    return 0;
  }

  var seats = parseInt(seatsElements[0].value);

  var myselfSelector = jQuery('#tx_seminars_pi1_registration_editor__registered_themselves');
  var selfSeat;
  if (myselfSelector.length > 0) {
    selfSeat = parseInt(myselfSelector.attr('value'));
  } else {
    var $defaultValue = jQuery('#tx-seminars-pi1-themselves-default');
    if ($defaultValue.length > 0) {
      selfSeat = parseInt($defaultValue.data('value'));
    } else {
      selfSeat = 1;
    }
  }

  return seats - selfSeat;
};

/**
 * Updates an auxiliary record after it has been edited in the FE editor.
 *
 * @param {String} htmlId
 *        the HTML ID of the auxiliary record checkbox label to update, must not
 *        be empty
 * @param {String} title the title of the auxiliary record, must not be empty
 */
TYPO3.seminars.updateAuxiliaryRecordInEditor = function(htmlId, title) {
  var labels = jQuery('#' + htmlId);
  if (labels.length === 0) {
    return;
  }

  labels[0].innerHTML = title;
};

/**
 * Appends an auxiliary record as a checkbox so that it is available for
 * selection in the FE editor.
 *
 * @param {Number} uid the UID of the record to add, must be > 0
 * @param {String} title the title of the record, must not be empty
 * @param {String} htmlName
 *        the relevant part of the IDs and names for the selection elements,
 *        e.g. "place", "speaker" or "tutor".
 * @param {Array} buttonData the data of the edit button of the record
 */
TYPO3.seminars.appendAuxiliaryRecordInEditor = function(uid, title, htmlName, buttonData) {
  var container = jQuery('#tx_seminars_pi1_seminars_' + htmlName + ' tbody')[0];
  if (!container) {
    return;
  }
  var nextOptionNumber = jQuery('#tx_seminars_pi1_seminars_' + htmlName + ' input').length;

  var id = 'tx_seminars_pi1_seminars_' + htmlName + '_' + nextOptionNumber;
  var input = new Element('input', {
    'id': id, 'type': 'checkbox', 'value': uid,
    'name': 'tx_seminars_pi1_seminars[' + htmlName + '][' + nextOptionNumber + ']',
    'class': 'tx-seminars-pi1-event-editor-checkbox'
  });
  var labelId = 'tx_seminars_pi1_seminars_' + htmlName + '_label_' + uid;
  var label = new Element('label', {'for': id, 'id': labelId});
  label.appendChild(document.createTextNode(title));

  var button = new Element(
    'input',
    {
      'type': 'button',
      'name': buttonData.name,
      'value': buttonData.value,
      'id': buttonData.id,
      'class': 'tx-seminars-pi1-event-editor-edit-button'
    }
  );

  var tableRow = new Element('tr');
  var tableColumnLeft = new Element('td');
  var tableColumnRight = new Element('td');

  tableColumnLeft.appendChild(input);
  tableColumnLeft.appendChild(label);
  tableColumnRight.appendChild(button);
  tableRow.appendChild(tableColumnLeft);
  tableRow.appendChild(tableColumnRight);

  container.appendChild(tableRow);
};

/**
 * Appends a place so that it is available for selection in the FE editor.
 *
 * @param {Number} uid the UID of the place to add, must be > 0
 * @param {String} title the title of the place, must not be empty
 * @param {Array} buttonData the data of the edit button of the place
 */
TYPO3.seminars.appendPlaceInEditor = function(uid, title, buttonData) {
  TYPO3.seminars.appendAuxiliaryRecordInEditor(uid, title, "place", buttonData);
};

/**
 * Appends a speaker so that it is available for selection in the FE editor.
 *
 * @param {Number} uid the UID of the speaker to add, must be > 0
 * @param {String} title the name of the speaker, must not be empty
 * @param {Array} buttonData the data of the edit button of the speaker
 */
TYPO3.seminars.appendSpeakerInEditor = function(uid, title, buttonData) {
  TYPO3.seminars.appendAuxiliaryRecordInEditor(uid, title, 'speakers', buttonData);
  TYPO3.seminars.appendAuxiliaryRecordInEditor(uid, title, 'leaders', buttonData);
  TYPO3.seminars.appendAuxiliaryRecordInEditor(uid, title, 'partners', buttonData);
  TYPO3.seminars.appendAuxiliaryRecordInEditor(uid, title, 'tutors', buttonData);
};

/**
 * Appends a checkbox so that it is available for selection in the FE editor.
 *
 * @param {Number} uid the UID of the checkbox to add, must be > 0
 * @param {String} title the title of the checkbox, must not be empty
 * @param {Array} buttonData the data of the edit button of the checkbox
 */
TYPO3.seminars.appendCheckboxInEditor = function(uid, title, buttonData) {
  TYPO3.seminars.appendAuxiliaryRecordInEditor(uid, title, 'checkboxes', buttonData);
};

/**
 * Appends a target group so that it is available for selection in the FE editor.
 *
 * @param {Number} uid the UID of the target group to add, must be > 0
 * @param {String} title the title of the target group, must not be empty
 * @param {Array} buttonData the data of the edit button of the target group
 */
TYPO3.seminars.appendTargetGroupInEditor = function(uid, title, buttonData) {
  TYPO3.seminars.appendAuxiliaryRecordInEditor(uid, title, 'target_groups', buttonData);
};

/**
 * Clears the selection of the search widget.
 */
TYPO3.seminars.clearSearchWidgetFields = function() {
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
};

/**
 * Converts the links that have a data-method="post" to JavaScript-powered on-the-fly forms.
 */
TYPO3.seminars.convertActionLinks = function() {
  jQuery('.tx-seminars-pi1 a[data-method]').click(TYPO3.seminars.executeLinkAction);
};

/**
 * Executes the action on a link.
 *
 * @param {MouseEvent} event
 */
TYPO3.seminars.executeLinkAction = function(event) {
  var linkElement = event.target;
  var linkHref = linkElement.getAttribute('href');

  TYPO3.seminars.disableAllActionLinks();

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
};

/**
 * Disables all action links (so that they cannot be clicked again once an action is being processed).
 */
TYPO3.seminars.disableAllActionLinks = function() {
  var linkElements = document.querySelectorAll('a[data-method]');
  for (var i = 0; i < linkElements.length; i++) {
    linkElements[i].onclick = function() {
      return false;
    };
  }
};

/**
 * Prevents registration form submit event to be called twice.
 */
TYPO3.seminars.preventMultipleFormSubmit = function() {
  var submitForm = document.getElementById('tx_seminars_pi1_registration_editor');
  var submitButton = document.getElementById('tx_seminars_pi1_registration_editor__button_submit');
  submitForm.addEventListener('submit', function(event) {
    if (submitButton.hasAttribute('disabled')) {
      event.preventDefault();
    }
  });
}

/**
 * Initializes the search widget.
 */
TYPO3.seminars.initializeSearchWidget = function() {
  if (jQuery('.tx-seminars-pi1-selectorwidget').length === 0) {
    return;
  }

  jQuery('#tx-seminars-pi1-clear-search-widget').click(function() {
    TYPO3.seminars.clearSearchWidgetFields();
  });
};

/**
 * This method updates the UI if anything corresponding the number of seats has changed.
 */
TYPO3.seminars.updateAttendees = function() {
  TYPO3.seminars.fixNameFieldsNumber();
  TYPO3.seminars.compileNames();
};

TYPO3.seminars.initializeLegacyRegistrationForm = function() {
  var registrationForm = jQuery('#tx-seminars-pi1-registration-form');
  if (registrationForm.length === 0) {
    return;
  }

  registrationForm.find('#tx_seminars_pi1_registration_editor_separate_names').on('blur', 'input', TYPO3.seminars.compileNames);
  registrationForm.find('#tx_seminars_pi1_registration_editor__seats').change(TYPO3.seminars.updateAttendees);
  registrationForm.find('#tx_seminars_pi1_registration_editor__registered_themselves_checkbox').click(TYPO3.seminars.updateAttendees);

  TYPO3.seminars.fixNameFieldsNumber();
  TYPO3.seminars.restoreSeparateNameFields();
  TYPO3.seminars.compileNames();
  TYPO3.seminars.preventMultipleFormSubmit();
};

TYPO3.seminars.findRegistrationFormElements = function() {
  const selectors = {
    registrationForm: 'form[data-behavior="tx-seminars-registration-form"]',
    billingAddressCheckbox: 'input[data-behavior="tx-seminars-billing-address-toggle"]',
    billingAddressFields: '[data-behavior="tx-seminars-billing-address-fields"]',
    seats: '[data-behavior="tx-seminars-seats"]',
    registeredThemselves: '[data-behavior="tx-seminars-registered-themselves"]',
    attendeesNames: '[data-behavior="tx-seminars-attendees-names"]',
  }

  for (const key in selectors) {
    TYPO3.seminars.elements[key] = document.querySelector(selectors[key]);
  }
};

TYPO3.seminars.existsRegistrationForm = function() {
  return TYPO3.seminars.elements.registrationForm instanceof Element;
}

TYPO3.seminars.initializeRegistrationForm = function() {
  TYPO3.seminars.findRegistrationFormElements();
  if (!TYPO3.seminars.existsRegistrationForm()) {
    return;
  }

  TYPO3.seminars.updateBillingAddressVisibility();
  TYPO3.seminars.addBillingAddressCheckboxListener();

  TYPO3.seminars.updateAttendeesNamesVisibility();
  TYPO3.seminars.addSeatsListener();
};

TYPO3.seminars.addBillingAddressCheckboxListener = function() {
  if (!(TYPO3.seminars.elements.billingAddressCheckbox instanceof Element)) {
    return;
  }

  TYPO3.seminars.elements.billingAddressCheckbox.addEventListener('change', TYPO3.seminars.updateBillingAddressVisibility);
}

TYPO3.seminars.addSeatsListener = function() {
  if (TYPO3.seminars.elements.seats instanceof Element) {
    TYPO3.seminars.elements.seats.addEventListener('change', TYPO3.seminars.updateAttendeesNamesVisibility);
  }
  if (TYPO3.seminars.elements.registeredThemselves instanceof Element) {
    TYPO3.seminars.elements.registeredThemselves
      .addEventListener('change', TYPO3.seminars.updateAttendeesNamesVisibility);
  }
}

TYPO3.seminars.updateBillingAddressVisibility = function() {
  if (!(TYPO3.seminars.elements.billingAddressCheckbox instanceof Element)
    || !(TYPO3.seminars.elements.billingAddressFields instanceof Element)
  ) {
    return;
  }

  const shouldShowBillingAddress = !!TYPO3.seminars.elements.billingAddressCheckbox.checked;
  if (shouldShowBillingAddress) {
    TYPO3.seminars.showElement(TYPO3.seminars.elements.billingAddressFields);
  } else {
    TYPO3.seminars.hideElement(TYPO3.seminars.elements.billingAddressFields);
  }
};

TYPO3.seminars.updateAttendeesNamesVisibility = function() {
  if (!(TYPO3.seminars.elements.attendeesNames instanceof Element)) {
    return;
  }

  let seats = 1;
  if (TYPO3.seminars.elements.seats instanceof Element) {
    seats = parseInt(TYPO3.seminars.elements.seats.value);
  }
  let registeredThemselves = true;
  if (TYPO3.seminars.elements.registeredThemselves instanceof Element) {
    registeredThemselves = !!TYPO3.seminars.elements.registeredThemselves.checked;
  }
  const otherSeats = seats - (registeredThemselves ? 1 : 0);
  const shouldShowAttendeesNames = otherSeats > 0;

  if (shouldShowAttendeesNames) {
    TYPO3.seminars.showElement(TYPO3.seminars.elements.attendeesNames);
  } else {
    TYPO3.seminars.hideElement(TYPO3.seminars.elements.attendeesNames);
  }
};

TYPO3.seminars.showElement = function(element) {
  if (!(element instanceof Element)) {
    return;
  }

  for (const classToAdd of TYPO3.seminars.visibilityClasses) {
    element.classList.add(classToAdd);
  }
  for (const classToRemove of TYPO3.seminars.invisibilityClasses) {
    element.classList.remove(classToRemove);
  }
}

TYPO3.seminars.hideElement = function(element) {
  for (const classToAdd of TYPO3.seminars.invisibilityClasses) {
    element.classList.add(classToAdd);
  }
  for (const classToRemove of TYPO3.seminars.visibilityClasses) {
    element.classList.remove(classToRemove);
  }
}

document.addEventListener('readystatechange', function() {
  TYPO3.seminars.initializeSearchWidget();
  TYPO3.seminars.initializeLegacyRegistrationForm();
  TYPO3.seminars.convertActionLinks();

  TYPO3.seminars.initializeRegistrationForm();
});
