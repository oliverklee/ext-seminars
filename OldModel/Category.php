<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class tx_seminars_OldModel_Category for the "seminars" extension.
 *
 * This class represents an event category.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_OldModel_Category extends tx_seminars_OldModel_Abstract {
	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = 'tx_seminars_categories';

	/**
	 * the class name of the mapper responsible for creating the new model
	 * that corresponds to this old model
	 *
	 * @var string
	 */
	protected $mapperName = 'tx_seminars_Mapper_Category';

	/**
	 * Returns the icon of this category.
	 *
	 * @return string the file name of the icon (relative to the extension
	 *                upload path) of the category, will be empty if the
	 *                category has no icon
	 */
	public function getIcon() {
		return $this->getRecordPropertyString('icon');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/OldModel/Category.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/OldModel/Category.php']);
}
?>