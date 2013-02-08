<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Niels Pardon (mail@niels-pardon.de)
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
 * Fixture for testing purposes. Makes some protected functions public so we can test them.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList extends tx_seminars_BackEndExtJs_Ajax_AbstractList {
	/**
	 * @var string the class name of the mapper to use to create the list, which
	 *             is a non-existent fake mapper in this case
	 */
	protected $mapperName = 'tx_oelib_tests_fixtures_TestingMapper';

	/**
	 * Returns the data of the given model in an array.
	 *
	 * Available array keys are: uid
	 *
	 * @param tx_oelib_Model $model the model to return the data from in array
	 *
	 * @return array the data of the given model
	 */
	public function getAsArray(tx_oelib_Model $model) {
		return parent::getAsArray($model);
	}

	/**
	 * @param tx_oelib_Model $model the model to return the data from in array
	 *
	 * @return array
	 */
	protected function getAdditionalFields(tx_oelib_Model $model) {
		return array();
	}

	/**
	 * Retrieves the models.
	 *
	 * @return tx_oelib_List will be a list of models in case of success, NULL
	 *                       in case of failure
	 */
	public function retrieveModels() {
		return parent::retrieveModels();
	}

	/**
	 * Checks whether the given page UID is a valid system folder.
	 *
	 * @param integer $pageUid the page UID to check, may be 0 or negative
	 *
	 * @return boolean TRUE if the given page UID is a valid system folder,
	 *                 FALSE otherwise
	 */
	public function isPageUidValid($pageUid) {
		return parent::isPageUidValid($pageUid);
	}

	/**
	 * Returns always TRUE.
	 *
	 * @return boolean always TRUE
	 */
	protected function hasAccess() {
		return TRUE;
	}
}
?>