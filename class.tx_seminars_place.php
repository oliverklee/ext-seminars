<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_place' for the 'seminars' extension.
 *
 * This class represents a place.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_place extends tx_seminars_objectfromdb {
	/** string with the name of the SQL table this class corresponds to */
	var $tableName = SEMINARS_TABLE_SITES;

	/**
	 * Returns the name of the city of this place record.
	 *
	 * This should not return an empty string as the city field is a required
	 * field. But records that existed before, an empty string will be returned
	 * as they don't have the city set yet.
	 *
	 * @return string the name of the city, will be empty if the place
	 *                record has no city set
	 *
	 * @access public
	 */
	function getCity() {
		return $this->getRecordPropertyString('city');
	}

	/**
	 * Returns the ISO 3166-1 alpha-2 code for the country of this place or an
	 * empty string if this place has no country set.
	 *
	 * This method does not validate the value of the saved field value. As the
	 * country is selected and saved through the backend from a prefilled list,
	 * those values should be valid.
	 *
	 * @return string the ISO 3166-1 alpha-2 code of the country or an
	 *                empty string if this place has no country set
	 *
	 * @access public
	 */
	function getCountryIsoCode() {
		return $this->getRecordPropertyString('country');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_place.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_place.php']);
}
?>