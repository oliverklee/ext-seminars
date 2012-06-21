<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_Model_EventType' for the 'seminars' extension.
 *
 * This class represents a event type.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_EventType extends tx_oelib_Model implements tx_seminars_Interface_Titled {
	/**
	 * Returns our title.
	 *
	 * @return string our title, will not be empty
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Sets our title.
	 *
	 * @param string $title our title to set, must not be empty
	 */
	public function setTitle($title) {
		if ($title == '') {
			throw new InvalidArgumentException('The parameter $title must not be empty.', 1333296812);
		}

		$this->setAsString('title', $title);
	}

	/**
	 * Gets the UID of the single view page for events of this type.
	 *
	 * @return integer the single view page, will be 0 if none has been set
	 */
	public function getSingleViewPageUid() {
		return $this->getAsInteger('single_view_page');
	}

	/**
	 * Checks whether this event type has a single view page UID set.
	 *
	 * @return boolean
	 *         TRUE if this event type has a single view page set, FALSE
	 *         otherwise
	 */
	public function hasSingleViewPageUid() {
		return $this->hasInteger('single_view_page');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/EventType.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/EventType.php']);
}
?>