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
 * This class provides an access check for the CSV export in the back end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Seminars_Csv_AbstractBackEndAccessCheck implements Tx_Seminars_Interface_CsvAccessCheck {
	/**
	 * @var int
	 *
	 * @see t3lib_BEfunc::getRecord
	 */
	const SHOW_PAGE_PERMISSION_BITS = 1;

	/**
	 * @var int
	 */
	protected $pageUid = 0;

	/**
	 * Sets the page UID of the records.
	 *
	 * @param int $pageUid the page UID of the records, must be >= 0
	 *
	 * @return void
	 */
	public function setPageUid($pageUid) {
		$this->pageUid = $pageUid;
	}

	/**
	 * Returns the page UID of the records to check.
	 *
	 * @return int the page UID, will be >= 0
	 */
	protected function getPageUid() {
		return $this->pageUid;
	}

	/**
	 * Checks whether the currently logged-in BE-User is allowed to access the given table and page.
	 *
	 * @param string $tableName
	 *        the name of the table to check the read access for, must not be empty
	 *
	 * @param int $pageUid the page to check the access for, must be >= 0
	 *
	 * @return bool TRUE if the user has access to the given table and page,
	 *                 FALSE otherwise, will also return FALSE if no BE user is logged in
	 */
	protected function canAccessTableAndPage($tableName, $pageUid) {
		if (!Tx_Oelib_BackEndLoginManager::getInstance()->isLoggedIn()) {
			return FALSE;
		}

		return $this->hasReadAccessToTable($tableName) && $this->hasReadAccessToPage($pageUid);
	}

	/**
	 * Returns the logged-in back-end user.
	 *
	 * @return t3lib_beUserAuth
	 */
	protected function getLoggedInBackEndUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Checks whether the logged-in back-end user has read access to the table $tableName.
	 *
	 * @param string $tableName the table name to check, must not be empty
	 *
	 * @return bool
	 */
	protected function hasReadAccessToTable($tableName) {
		return $this->getLoggedInBackEndUser()->check('tables_select', $tableName);
	}

	/**
	 * Checks whether the logged-in back-end user has read access to the page (or folder) with the UID $pageUid.
	 *
	 * @param int $pageUid the page to check the access for, must be >= 0
	 *
	 * @return bool
	 */
	protected function hasReadAccessToPage($pageUid) {
		return $this->getLoggedInBackEndUser()
			->doesUserHaveAccess(t3lib_BEfunc::getRecord('pages', $pageUid), self::SHOW_PAGE_PERMISSION_BITS);
	}
}