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
 * This interface needs to be used for hooks concerning the back-end module.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_seminars_Interface_Hook_BackEndModule {
	/**
	 * Modifies the general e-mail sent via the back-end module.
	 *
	 * Note: This hook does not get called yet. It is just here so the interface
	 * is finalized.
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to which the e-mail refers
	 * @param tx_oelib_Mail $eMail
	 *        the e-mail that will be sent
	 *
	 * @return void
	 */
	public function modifyGeneralEmail(tx_seminars_Model_Registration $registration, tx_oelib_Mail $eMail);

	/**
	 * Modifies the confirmation e-mail sent via the back-end module.
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to which the e-mail refers
	 * @param tx_oelib_Mail $eMail
	 *        the e-mail that will be sent
	 *
	 * @return void
	 */
	public function modifyConfirmEmail(tx_seminars_Model_Registration $registration, tx_oelib_Mail $eMail);

	/**
	 * Modifies the cancelation e-mail sent via the back-end module.
	 *
	 * Note: This hook does not get called yet. It is just here so the interface
	 * is finalized.
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to which the e-mail refers
	 * @param tx_oelib_Mail $eMail
	 *        the e-mail that will be sent
	 *
	 * @return void
	 */
	public function modifyCancelEmail(tx_seminars_Model_Registration $registration, tx_oelib_Mail $eMail);
}
?>