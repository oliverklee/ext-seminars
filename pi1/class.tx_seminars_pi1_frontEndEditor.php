<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'tx_seminars_pi1_frontEndEditor' for the 'seminars' extension.
 *
 * This class is the base class for any kind of front-end editor, for example
 * the event editor or the registration editor.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_pi1_frontEndEditor extends tx_oelib_templatehelper {
	/**
	 * Provides data items from the DB.
	 *
	 * By default, the field "title" is used as the name that will be returned
	 * within the array (as caption). For FE users, the field "name" is used.
	 *
	 * @param array array that contains any pre-filled data, may be empty
	 * @param string the table name to query, must not be empty
	 * @param string query parameter that will be used as the WHERE clause, must
	 *               not be empty
	 * @param boolean whether to append a <br /> at the end of each caption
	 *
	 * @return array $items with additional items from the $params['what']
	 *               table as an array with the keys "caption" (for the title)
	 *               and "value" (for the UID), might be empty
	 */
	public function populateList(
		array $items, $tableName, $queryParameters = '1=1', $appendBreak = false
	) {
		$result = $items;

		$titleSuffix = ($appendBreak) ? '<br />' : '';
		$captionField = ($tableName == 'fe_users') ? 'name' : 'title';

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$tableName,
			$queryParameters . tx_oelib_db::enableFields($tableName)
		);
		if (!$dbResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}

		while ($dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			$uid = $dbResultRow['uid'];
			$title = $dbResultRow[$captionField];

			$result[$uid] = array(
				'caption' => $title . $titleSuffix,
				'value' => $uid
			);
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndEditor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndEditor.php']);
}
?>