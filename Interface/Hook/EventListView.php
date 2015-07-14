<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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