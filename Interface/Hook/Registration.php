<?php
/***************************************************************
* Copyright notice
*
* (c) 2013-2014 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This interface needs to be used for hooks concerning the registration process.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_seminars_Interface_Hook_Registration {
	/**
	 * Modifies the registration notification e-mail to an organizer.
	 *
	 * @param tx_seminars_registration $registration
	 * @param tx_oelib_Template $emailTemplate
	 *
	 * @return void
	 */
	public function modifyOrganizerNotificationEmail(tx_seminars_registration $registration, tx_oelib_Template $emailTemplate);

	/**
	 * Modifies the registration or unregistration e-mail to an attendee.
	 *
	 * @param tx_seminars_registration $registration
	 * @param tx_oelib_Template $emailTemplate
	 *
	 * @return void
	 */
	public function modifyAttendeeEmailText(tx_seminars_registration $registration, tx_oelib_Template $emailTemplate);
}