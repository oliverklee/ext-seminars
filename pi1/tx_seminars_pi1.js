/***************************************************************
* Copyright notice
*
* (c) 2008 Saskia Metzler <saskia@merlin.owl.de>
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
 * This file provides a JavaScript function to mark attachments as deleted in
 * the FE editor.
 *
 * @package		TYPO3
 * @subpackage	tx_realty
 *
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */

/**
 * Marks the current attachment as deleted if the confirm becomes submitted.
 * 
 * @param	string		ID of the list item with the attachment to delete, must
 * 						not be empty
 * @param	string		localized confirm message for whether really to mark an
 *						attachment for deletion
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