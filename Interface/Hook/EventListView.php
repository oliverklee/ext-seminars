<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This interface needs to be used for hooks concerning the event list view.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_seminars_Interface_Hook_EventListView {
	/**
	 * Modifies a list row in the events list.
	 *
	 * This function will be called for all types of event lists.
	 *
	 * @param tx_seminars_Model_Event $event
	 *        the event to display in the current row
	 * @param tx_oelib_Template $template
	 *        the template that will be used to create the list row output
	 *
	 * @return void
	 */
	public function modifyListRow(tx_seminars_Model_Event $event, tx_oelib_Template $template);

	/**
	 * Modifies a list view row in the "my events" list.
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to display in the current row
	 * @param tx_oelib_Template $template
	 *        the template that will be used to create the list row output
	 *
	 * @return void
	 */
	public function modifyMyEventsListRow(
		tx_seminars_Model_Registration $registration, tx_oelib_Template $template
	);
}