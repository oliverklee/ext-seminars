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
 * Class tx_seminars_BackEndExtJs_Ajax_StateProvider for the "seminars" extension.
 *
 * This class provides functionality for saving the state of ExtJS components
 * via AJAX in the back-end user configuration.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_StateProvider {
	/**
	 * Returns the ExtJS state data from the current back-end user config.
	 *
	 * @return array the ExtJS state data in "data" and "success" => TRUE
	 */
	public function getState() {
		$data = array();

		foreach ($GLOBALS['BE_USER']->uc['tx_seminars_BackEndExtJs_State'] as $name => $value) {
			$data[] = array('name' => $name, 'value' => $value);
		}

		return array(
			'success' => TRUE,
			'data' => $data,
		);
	}

	/**
	 * Sets the ExtJS state data from $_POST to the current back-end user config.
	 *
	 * Returns "success" => FALSE if the name of the ExtJS component is empty or
	 * if the ExtJS state data could not be decoded.
	 *
	 * @return array "success" => TRUE or "success" => FALSE if an error occured
	 */
	public function setState() {
		$name = t3lib_div::_POST('name');
		if ($name == '') {
			return array('success' => FALSE);
		}

		$value = json_decode(t3lib_div::_POST('value'));
		if ($value === NULL) {
			return array('success' => FALSE);
		}

		$GLOBALS['BE_USER']->uc['tx_seminars_BackEndExtJs_State'][$name] = $value;
		$GLOBALS['BE_USER']->writeUC('tx_seminars_BackEndExtJs_State');

		return array('success' => TRUE);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/StateProvider.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/StateProvider.php']);
}
?>