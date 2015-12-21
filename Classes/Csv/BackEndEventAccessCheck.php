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