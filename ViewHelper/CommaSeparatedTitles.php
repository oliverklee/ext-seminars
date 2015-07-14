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