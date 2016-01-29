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
 * This interface needs to be used for hooks concerning the back-end module.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Seminars_Interface_Hook_BackEndModule {
	/**
	 * Modifies the general e-mail sent via the back-end module.
	 *
	 * Note: This hook does not get called yet. It is just here so the interface
	 * is finalized.
	 *
	 * @param Tx_Seminars_Model_Registration $registration
	 *        the registration to which the e-mail refers
	 * @param Tx_Oelib_Mail $eMail
	 *        the e-mail that will be sent
	 *
	 * @return void
	 */
	public function modifyGeneralEmail(Tx_Seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail);

	/**
	 * Modifies the confirmation e-mail sent via the back-end module.
	 *
	 * @param Tx_Seminars_Model_Registration $registration
	 *        the registration to which the e-mail refers
	 * @param Tx_Oelib_Mail $eMail
	 *        the e-mail that will be sent
	 *
	 * @return void
	 */
	public function modifyConfirmEmail(Tx_Seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail);

	/**
	 * Modifies the cancelation e-mail sent via the back-end module.
	 *
	 * Note: This hook does not get called yet. It is just here so the interface
	 * is finalized.
	 *
	 * @param Tx_Seminars_Model_Registration $registration
	 *        the registration to which the e-mail refers
	 * @param Tx_Oelib_Mail $eMail
	 *        the e-mail that will be sent
	 *
	 * @return void
	 */
	public function modifyCancelEmail(Tx_Seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail);
}