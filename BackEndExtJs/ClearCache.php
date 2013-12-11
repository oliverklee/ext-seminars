<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Niels Pardon (mail@niels-pardon.de)
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
 * This class is called by the clearCachePostProc hook in
 * t3lib_tcemain->clear_cacheCmd() and removes the cached language labels files
 * in typo3temp/tx_seminars_BackEndExtJS/ if the cache gets cleared.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_ClearCache {
	/**
	 * Removes all files in typo3temp/tx_seminars/BackEndExtJs/ if $cacheCmd is
	 * "all".
	 *
	 * This method is called by the clearCachePostProc hook in
	 * t3lib_tcemain->clear_cacheCmd().
	 *
	 * @param array $parameters the cache command from t3lib_tcemain->clear_cacheCmd() in the key "cacheCmd"
	 *
	 * @see t3lib_tcemain->clear_cacheCmd()
	 *
	 * @return void
	 */
	public function clearCachePostProcess($parameters) {
		if ($parameters['cacheCmd'] != 'all') {
			return;
		}

		t3lib_div::rmdir(
			PATH_site .
				tx_seminars_BackEndExtJs_Module::LANGUAGE_LABELS_CACHE_PATH,
			TRUE
		);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/ClearCache.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/ClearCache.php']);
}