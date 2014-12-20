<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012 Niels Pardon (mail@niels-pardon.de)
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
 * This class represents a view helper for rendering the elements of a list as comma separated titles.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_ViewHelper_CommaSeparatedTitles {
	/**
	 * Gets the titles of the elements in $list as a comma separated list.
	 *
	 * The titles will be htmlspecialchared before being returned.
	 *
	 * @param tx_oelib_List<tx_seminars_Interface_Titled> $list
	 *
	 * @return string the titles of the elements in $list as a comma separated list or an empty string if the list is empty
	 */
	public function render(tx_oelib_List $list) {
		$titles = array();

		/** @var tx_seminars_Interface_Titled $element */
		foreach ($list as $element) {
			if (!$element instanceof tx_seminars_Interface_Titled) {
				throw new InvalidArgumentException(
					'All elements in $list must implement the interface tx_seminars_Interface_Titled.', 1333658899
				);
			}

			$titles[] = htmlspecialchars($element->getTitle());
		}

		return implode(', ', $titles);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/ViewHelper/CommaSeparatedTitles.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/ViewHelper/CommaSeparatedTitles.php']);
}