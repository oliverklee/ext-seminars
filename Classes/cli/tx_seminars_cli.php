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
defined('TYPO3_cliMode') or die('You cannot run this script directly!');

setlocale(LC_NUMERIC, 'C');

try {
	/** @var tx_seminars_cli_MailNotifier $mailNotifier */
	$mailNotifier = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_seminars_cli_MailNotifier');
	$mailNotifier->start();
} catch (Exception $exception) {
	echo $exception->getMessage();
}