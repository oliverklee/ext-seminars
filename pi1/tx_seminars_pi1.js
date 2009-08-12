/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Saskia Metzler <saskia@merlin.owl.de>
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

/*
 * This file provides some JavaScript functions for the seminars front-end
 * editor and the registration form.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */

/**
 * Marks the current attachment as deleted if the confirm becomes submitted.
 *
 * @param string listItemId
 *        ID of the list item with the attachment to delete, must not be empty
 * @param string confirmMessage
 *        localized confirm message for whether really to mark an attachment for
 *        deletion
 */
function markAttachmentAsDeleted(listItemId, confirmMessage) {
	var listItem = document.getElementById(listItemId);
	var fileNameDiv = listItem.getElementsByTagName("span")[0];
	var deleteButton = listItem.getElementsByTagName("input")[0];

	if (confirm(confirmMessage)) {
		document.getElementById("tx_seminars_pi1_seminars_delete_attached_files").value
			+= "," + fileNameDiv.firstChild.nodeValue;
		fileNameDiv.setAttribute("class", "deleted");
		deleteButton.disabled = true;
	}
}

/**
 * Collects the names from the first/last name field pairs and compiles/inserts
 * them into the human-readable "additional attendees" field and the machine-readable
 * "structured attendees" field.
 */
function compileNames() {
	var humanReadableField = $("tx_seminars_pi1_registration_editor_attendees_names");
	var machineReadableField = $("tx_seminars_pi1_registration_editor_structured_attendees_names");

	if (!humanReadableField || !machineReadableField) {
		 return;
	}

	var firstNames = $$(".tx_seminars_pi1_registration_editor_first_name");
	var lastNames = $$(".tx_seminars_pi1_registration_editor_last_name");

	if (firstNames.length != lastNames.length) {
		return;
	}

	var humanReadableNames = "";
	var machineReadableNames = [];
	for (var i = 0; i < firstNames.length; i++) {
		var firstName = firstNames[i].value.strip();
		var lastName = lastNames[i].value.strip();

		if ((firstName.empty()) && (lastName.empty())) {
			continue;
		}

		var fullName = (firstName + " " + lastName).strip();
		if (!humanReadableNames.empty()) {
			humanReadableNames += "\r\n";
		}
		humanReadableNames += fullName;

		machineReadableNames[i] = [firstName, lastName];
	}

	humanReadableField.value = humanReadableNames;
	machineReadableField.value = machineReadableNames.toJSON();
}

/**
 * Restores the separate name fields from the hidden field with the names
 * in a JSON-encoded array.
 */
function restoreSeparateNameFields() {
	var machineReadableField = $("tx_seminars_pi1_registration_editor_structured_attendees_names");

	if (!machineReadableField || machineReadableField.value.empty()
		|| !machineReadableField.value.isJSON()) {
		return;
	}

	var firstNames = $$("#tx_seminars_pi1_registration_editor_separate_names "
		+ ".tx_seminars_pi1_registration_editor_first_name");
	var lastNames = $$("#tx_seminars_pi1_registration_editor_separate_names "
		+ ".tx_seminars_pi1_registration_editor_last_name");

	if (firstNames.length != lastNames.length) {
		return;
	}

	var allNames = machineReadableField.value.evalJSON(true);
	var numberOfNames = Math.min(firstNames.length, allNames.length);

	for (var i = 0; i < numberOfNames; i++) {
		firstNames[i].value = allNames[i][0];
		lastNames[i].value = allNames[i][1];
	}
}

/**
 * Adds or drops name fields to match the number of selected seats.
 */
function fixNameFieldsNumber() {
	var neededNameLines = getNumberOfNeededNameFields();
	var nameLines = $$("#tx_seminars_pi1_registration_editor_separate_names "
		+ ".tx_seminars_pi1_registration_editor_name_line");

	if (nameLines.length < neededNameLines) {
		var nameLineTemplate =
			$$("#tx_seminars_pi1_registration_editor_name_template "
				+".tx_seminars_pi1_registration_editor_name_line")[0];
		var nameLinesContainer =
			$("tx_seminars_pi1_registration_editor_separate_names");

		for (var i = nameLines.length; i < neededNameLines; i++) {
			nameLinesContainer.appendChild(nameLineTemplate.cloneNode(true));
		}
	} else if (nameLines.length > neededNameLines) {
		for (var i = nameLines.length; i > neededNameLines; i--) {
			nameLines[i - 1].remove();
		}
	}
}

/**
 * Gets the number of needed name fields.
 *
 * @return integer the number of needed name fields, will be >= 0
 */
function getNumberOfNeededNameFields() {
	var seatsSelector = $("tx_seminars_pi1_registration_editor_seats");
	if (!seatsSelector) {
		return 0;
	}

	var seats = parseInt(seatsSelector.value);

	var myselfSelector
		= $("tx_seminars_pi1_registration_editor_registered_themselves");
	var selfSeat;
	if (myselfSelector) {
		selfSeat = myselfSelector.checked ? 1 : 0;
	} else {
		selfSeat = 1;
	}

	return seats - selfSeat;
}

/**
 * Updates an auxiliary record after it has been edited in the FE editor.
 *
 * @param string htmlId
 *        the HTML ID of the auxiliary record checkbox label to update, must not
 *        be empty
 * @param string title the title of the auxiliary record, must not be empty
 */
function updateAuxiliaryRecordInEditor(htmlId, title) {
	var label = $(htmlId);
	if (!label) {
		return;
	}

	label.innerHTML = title;
}

/**
 * Appends an auxiliary record as a checkbox so that it is available for
 * selection in the FE editor.
 *
 * @param integer uid the UID of the record to add, must be > 0
 * @param string title the title of the record, must not be empty
 * @param string htmlName
 *        the relevant part of the IDs and names for the selection elements,
 *        e.g. "place", "speaker" or "tutor".
 * @param string buttonHtml
 *        the HTML of the edit button of the record, may be empty
 */
function appendAuxiliaryRecordInEditor(uid, title, htmlName, buttonHtml) {
	var container = $("tx_seminars_pi1_seminars_" + htmlName);
	if (!container) {
		return;
	}
	var nextOptionNumber
		= $$("#tx_seminars_pi1_seminars_" + htmlName + " input").length;

	var id = "tx_seminars_pi1_seminars_" + htmlName + "_" + nextOptionNumber;
	var input = new Element("input", {
		"id": id, "type": "checkbox", "value": uid,
		"name" :
			"tx_seminars_pi1_seminars[" + htmlName + "][" + nextOptionNumber + "]"
	});
	var labelId = "tx_seminars_pi1_seminars_" + htmlName + "_label_" + uid;
	var label = new Element("label", {"for": id, "id": labelId});
	label.appendChild(document.createTextNode(title));

	container.appendChild(new Element("br"));
	container.appendChild(input);
	container.appendChild(label);
	container.innerHTML = container.innerHTML + buttonHtml;
}

/**
 * Appends a place so that it is available for selection in the FE editor.
 *
 * @param integer uid the UID of the place to add, must be > 0
 * @param string title the title of the place, must not be empty
 * @param string buttonHtml
 *        the HTML of the edit button of the place, may be empty
 */
function appendPlaceInEditor(uid, title, buttonHtml) {
	appendAuxiliaryRecordInEditor(uid, title, "place", buttonHtml);
}

/**
 * Appends a speaker so that it is available for selection in the FE editor.
 *
 * @param integer uid the UID of the speaker to add, must be > 0
 * @param string title the name of the speaker, must not be empty
 */
function appendSpeakerInEditor(uid, title) {
	appendAuxiliaryRecordInEditor(uid, title, "speakers", "");
	appendAuxiliaryRecordInEditor(uid, title, "leaders", "");
	appendAuxiliaryRecordInEditor(uid, title, "partners", "");
	appendAuxiliaryRecordInEditor(uid, title, "tutors", "");
}

/**
 * Appends a checkbox so that it is available for selection in the FE editor.
 *
 * @param integer uid the UID of the checkbox to add, must be > 0
 * @param string title the title of the checkbox, must not be empty
 * @param string buttonHtml
 *        the HTML of the edit button of the checkbox, may be empty
 */
function appendCheckboxInEditor(uid, title, buttonHtml) {
	appendAuxiliaryRecordInEditor(uid, title, "checkboxes", buttonHtml);
}

/**
 * Appends a target group so that it is available for selection in the FE editor.
 *
 * @param integer uid the UID of the target group to add, must be > 0
 * @param string title the title of the target group, must not be empty
 * @param string buttonHtml
 *        the HTML of the edit button of the target group, may be empty
 */
function appendTargetGroupInEditor(uid, title, buttonHtml) {
	appendAuxiliaryRecordInEditor(uid, title, "target_groups", buttonHtml);
}
