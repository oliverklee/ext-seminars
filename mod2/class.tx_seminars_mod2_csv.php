<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'pi2/class.tx_seminars_pi2.php');

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
class tx_seminars_mod2_csv extends tx_seminars_mod2_BackEndModule {
	/**
	 * Creates the CSV export content and outputs it directly on the page (in
	 * this case, for download).
	 */
	public function printContent() {
		$pi2 = t3lib_div::makeInstance('tx_seminars_pi2');
		echo $pi2->main(null, array());
		$pi2->__destruct();
		unset($pi2);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_csv.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_csv.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_seminars_mod2_csv');
$SOBE->init();
$SOBE->printContent();
?>