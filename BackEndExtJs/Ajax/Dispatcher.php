<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Class tx_seminars_BackEndExtJs_Ajax_Dispatcher for the "seminars" extension.
 *
 * This class is called by the ExtJS back-end module via AJAX using ajax.php.
 * It dispatches the AJAX call according to the given AJAX ID.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_Dispatcher {
	/**
	 * Dispatches the AJAX request.
	 *
	 * @param array $parameters
	 *        the parameters passed by the AJAX call, maybe empty, currently
	 *        unused
	 * @param TYPO3AJAX $ajaxObject
	 *        the AJAX object used to set the content and content-type of the
	 *        response of the AJAX call
	 */
	public function dispatch(array $parameters, TYPO3AJAX $ajaxObject) {
		switch ($ajaxObject->getAjaxID()) {
			case 'Seminars::getEvents':
				$result = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEndExtJs_Ajax_EventsList'
				)->createList();
				break;
			case 'Seminars::getRegistrations':
				$result = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEndExtJs_Ajax_RegistrationsList'
				)->createList();
				break;
			case 'Seminars::getSpeakers':
				$result = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEndExtJs_Ajax_SpeakersList'
				)->createList();
				break;
			case 'Seminars::getOrganizers':
				$result = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEndExtJs_Ajax_OrganizersList'
				)->createList();
				break;
			case 'Seminars::getState':
				$result = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEndExtJs_Ajax_StateProvider'
				)->getState();
				break;
			case 'Seminars::setState':
				$result = tx_oelib_ObjectFactory::make(
					'tx_seminars_BackEndExtJs_Ajax_StateProvider'
				)->setState();
				break;
			default:
				$result = array('success' => FALSE);
				break;
		}

		$ajaxObject->setContentFormat('json');
		$ajaxObject->setContent($result);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/Dispatcher.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/Dispatcher.php']);
}
?>