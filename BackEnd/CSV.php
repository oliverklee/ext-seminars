<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2012 Oliver Klee (typo3-coding@oliverklee.de)
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

unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');

// This checks permissions and exits if the users has no access to this page.
$BE_USER->modAccess($MCONF, 1);

/**
 * BE CSV export module for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEnd_CSV extends tx_seminars_BackEnd_Module {
	/**
	 * Creates the CSV export content and outputs it directly on the page (in
	 * this case, for download).
	 *
	 * @return void
	 */
	public function printContent() {
		$pi2 = tx_oelib_ObjectFactory::make('tx_seminars_pi2');
		echo $pi2->main(NULL, array());
		$pi2->__destruct();
		unset($pi2);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/CSV.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/CSV.php']);
}

// Make instance:
$SOBE = tx_oelib_ObjectFactory::make('tx_seminars_BackEnd_CSV');
$SOBE->init();
$SOBE->printContent();
?>