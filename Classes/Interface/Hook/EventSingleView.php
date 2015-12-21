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
 * This interface needs to be used for hooks concerning the event single view.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_seminars_Interface_Hook_EventSingleView {
	/**
	 * Modifies the event single view.
	 *
	 * @param tx_seminars_Model_Event $event
	 *        the event to display in the single view
	 * @param tx_oelib_Template $template
	 *        the template that will be used to create the single view output
	 *
	 * @return void
	 */
	public function modifyEventSingleView(tx_seminars_Model_Event $event, tx_oelib_Template $template);

	/**
	 * Modifies a list row in the time slots list (which is part of the event
	 * single view).
	 *
	 * @param tx_seminars_Model_TimeSlot $timeSlot
	 *        the time slot to display in the current row
	 * @param tx_oelib_Template $template
	 *        the template that will be used to create the list row output
	 *
	 * @return void
	 */
	public function modifyTimeSlotListRow(tx_seminars_Model_TimeSlot $timeSlot, tx_oelib_Template $template);
}