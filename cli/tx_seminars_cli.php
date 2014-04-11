<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2014 Niels Pardon (mail@niels-pardon.de)
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
 * CLI script.
 *
 * Any functionality is supposed to be provided by foreign classes because this
 * script is not testable as it must not be called in any other than the
 * TYPO3_cliMode.
 *
 * To run this script on your command line or via cronjob, use this command:
 * /[absolute TYPO3 path]/typo3/cli_dispatch.phpsh seminars [configuration page UID]
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
if (!defined('TYPO3_cliMode')) {
	die('You cannot run this script directly!');
}

setlocale(LC_NUMERIC, 'C');

try {
	t3lib_div::makeInstance('tx_seminars_cli_MailNotifier')->start();
} catch (Exception $exception) {
	echo $exception->getMessage();
}