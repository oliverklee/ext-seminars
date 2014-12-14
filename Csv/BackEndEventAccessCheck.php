<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Oliver Klee (typo3-coding@oliverklee.de)
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
 * This class provides the access check for the CSV export of events in the back end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Csv_BackEndEventAccessCheck extends Tx_Seminars_Csv_AbstractBackEndAccessCheck {
	/**
	 * @var string
	 */
	const TABLE_NAME_EVENTS = 'tx_seminars_seminars';

	/**
	 * Checks whether the logged-in user (if any) in the current environment has access to a CSV export.
	 *
	 * @return bool whether the logged-in user (if any) in the current environment has access to a CSV export.
	 */
	public function hasAccess() {
		return $this->canAccessTableAndPage(self::TABLE_NAME_EVENTS, $this->getPageUid());
	}
}