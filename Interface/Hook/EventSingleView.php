<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2012 Oliver Klee <typo3-coding@oliverklee.de>
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
?>