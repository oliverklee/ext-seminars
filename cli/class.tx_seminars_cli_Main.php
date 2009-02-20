<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Saskia Metzler <saskia@merlin.owl.de>
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
 * Class 'tx_seminars_cli_Main' for the 'seminars' extension.
 *
 * This class provides funcionality for the command line interface.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_seminars_cli_Main {
	/**
	 * Starts the CLI module.
	 */
	public function start() {
		$this->setConfigurationPage();
	}

	/**
	 * Checks whether the UID provided as the second argument when starting the
	 * CLI script actually exists in the "pages" table. If the page UID is
	 * valid, defines this UID as the one where to take the configuration from,
	 * otherwise throws an exception.
	 *
	 * @throws Exception if no page UID or an invalid UID was provided
	 */
	private function setConfigurationPage() {
		if (!isset($_SERVER['argv'][1])) {
			throw new Exception(
				'Please provide the UID for the page with the configuration ' .
				'for the CLI module.'
			);
		}

		$uid = intval($_SERVER['argv'][1]);
		if (($uid == 0) ||
			(tx_oelib_db::selectSingle(
				'COUNT(*) AS number', 'pages', 'uid = ' . $uid
			) != array('number' => 1))
		) {
			throw new Exception(
				'The provided UID for the page with the configuration was ' .
				$_SERVER['argv'][1] . ', which was not found to be a UID of ' .
				'an existing page. Please provide the UID of an existing page.'
			);
		}

		tx_oelib_PageFinder::getInstance()->setPageUid($uid);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/cli/class.tx_seminars_cli_Main.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realty/cli/class.tx_seminars_cli_Main.php']);
}
?>