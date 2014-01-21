<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2014 Oliver Klee (typo3-coding@oliverklee.de)
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
 * BE CSV export module.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */

unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once(t3lib_extMgm::extPath('seminars') . 'tx_seminars_modifiedSystemTables.php');

// This checks permissions and exits if the users has no access to this page.
$GLOBALS['BE_USER']->modAccess($MCONF, 1);

/** @var $csvExporter tx_seminars_pi2 */
$csvExporter = t3lib_div::makeInstance('tx_seminars_pi2');
echo $csvExporter->main();