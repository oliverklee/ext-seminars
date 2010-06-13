<?php
/***************************************************************
* Copyright notice
*
* (c) 2010 Niels Pardon (mail@niels-pardon.de)
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
 * Class tx_seminars_BackEndExtJs_Ajax_AbstractList for the "seminars" extension.
 *
 * This class provides basic functionality for creating a list of models for
 * usage in an AJAX call.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class tx_seminars_BackEndExtJs_Ajax_AbstractList {
	/**
	 * the class name of the mapper to use to create the list
	 *
	 * @var string
	 */
	protected $mapperName = '';

	/**
	 * The constructor.
	 */
	public function __construct() {
		if ($this->mapperName == '') {
			throw new Exception('No mapper class name set.');
		}
	}

	/**
	 * Creates the list and returns it as an array.
	 *
	 * @return array the created list in "rows" and "success" => TRUE,
	 *               or "success" => FALSE in case of an error
	 */
	public function createList() {
		$models = $this->retrieveModels();

		if ($models === null) {
			return array('success' => FALSE);
		}

		$rows = array();
		foreach ($models as $model) {
			$rows[] = $this->getAsArray($model);
		}

		return array(
			'success' => TRUE,
			'rows' => $rows,
		);
	}

	/**
	 * Returns the data of the given model in an array.
	 *
	 * Available array keys are: uid
	 *
	 * Additional keys are those provided by getAdditionalFields().
	 *
	 * @param tx_oelib_Model $model the model to return the data from in array
	 *
	 * @return array the data of the given model
	 *
	 * @see tx_seminars_BackEndExtJs_Ajax_AbstractList::getAdditionalFields()
	 */
	protected function getAsArray(tx_oelib_Model $model) {
		return array_merge(
			array('uid' => $model->getUid()),
			$this->getAdditionalFields($model)
		);
	}

	/**
	 * Returns additional fields of the given model as an array.
	 *
	 * @return array additional fields of the given model with the name of the
	 *               field as the key
	 *
	 * @see tx_seminars_BackEndExtJs_Ajax_AbstractList::getAsArray()
	 */
	abstract protected function getAdditionalFields(tx_oelib_Model $model);

	/**
	 * Retrieves the models that get listed by this list.
	 *
	 * @return tx_oelib_List will be a list of models in case of success, null
	 *                       in case of failure
	 */
	protected function retrieveModels() {
		$pageUid = intval(t3lib_div::_POST('id'));
		if (!$this->isPageUidValid($pageUid)) {
			return;
		}

		return tx_oelib_MapperRegistry::get($this->getMapperName())
			->findByPageUid(
				tx_oelib_db::createRecursivePageList($pageUid, 255)
			);
	}

	/**
	 * Checks whether the given page UID refers to a valid, existing system
	 * folder.
	 *
	 * @param integer $pageUid the page UID to check, may also be 0 or negative
	 *
	 * @return boolean TRUE if $pageUid is a valid system folder, FALSE otherwise
	 */
	protected function isPageUidValid($pageUid) {
		if ($pageUid <= 0) {
			return FALSE;
		}

		return tx_oelib_db::existsRecordWithUid(
			'pages',
			$pageUid,
			' AND doktype = 254' . tx_oelib_db::enableFields('pages', 1)
		);
	}

	/**
	 * Returns the mapper name in $this->mapperName.
	 *
	 * @return string the mapper name in $this->mapperName, will not be empty
	 */
	public function getMapperName() {
		return $this->mapperName;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/AbstractList.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEndExtJs/Ajax/AbstractList.php']);
}
?>