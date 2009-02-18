<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(PATH_formidableapi);

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
class tx_seminars_pi1_frontEndEditor extends tx_seminars_pi1_frontEndView {
	/**
	 * @var tx_ameosformidable object that creates the form
	 */
	private $formCreator = null;

	/**
	 * @var integer UID of the currently edited object, zero if the object is
	 *              going to be a new database record
	 */
	private $objectUid = 0;

	/**
	 * @var string the path to the FORMidable XML file
	 */
	private $xmlPath;

	/**
	 * @var boolean whether the class ist used in test mode
	 */
	private $isTestMode = false;

	/**
	 * @var array this is used to fake form values for testing
	 */
	private $fakedFormValues = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->formCreator);
		parent::__destruct();
	}

	/**
	 * Sets the current UID.
	 *
	 * @param integer UID of the currently edited object. For creating a new
	 *                database record, $uid must be zero. $uid must not be < 0.
	 */
	public function setObjectUid($uid) {
		$this->objectUid = $uid;
	}

	/**
	 * Gets the current object UID.
	 *
	 * @return integer UID of the currently edited object, zero if a new object
	 *                 is being created
	 */
	public function getObjectUid() {
		return $this->objectUid;
	}

	/**
	 * Sets the path to the FORMidable XML file to use.
	 *
	 * @param string path of the XML for the form, relative to this extension,
	 *               must not begin with a slash and must not be empty
	 */
	public function setXmlPath($path) {
		$this->xmlPath = $path;
	}

	/**
	 * Returns the FORMidable instance.
	 *
	 * @return tx_ameosformidable FORMidable instance or null if the test mode
	 *                            is set
	 */
	public function getFormCreator() {
		if (!$this->formCreator) {
			$this->formCreator = $this->makeFormCreator();
		}

		return $this->formCreator;
	}

	/**
	 * Enables the test mode. If this mode is activated, the FORMidable object
	 * will not be used at all, instead the faked form values will be taken.
	 */
	public function setTestMode() {
		$this->isTestMode = true;
	}

	/**
	 * Checks whether the test mode is set.
	 *
	 * @return boolean true if the test mode is set, false otherwise
	 */
	public function isTestMode() {
		return $this->isTestMode;
	}

	/**
	 * Returns the FE editor in HTML.
	 *
	 * Note that render() requires the FORMidable object to be initializable.
	 * This means that the test mode must not be set when calling render().
	 *
	 * @return string HTML for the FE editor or an error view if the
	 *                requested object is not editable for the current user
	 */
	public function render() {
		return $this->getFormCreator()->render();
	}

	/**
	 * Creates a FORMidable instance for the current UID and XML path. The UID
	 * must be of an existing seminar object.
	 *
	 * This function does nothing if this instance is running in test mode.
	 *
	 * @return tx_ameosformidable FORMidable instance or null if the test mode
	 *                            is set
	 */
	protected function makeFormCreator() {
		if ($this->isTestMode()) {
			return null;
		}

		if ($this->xmlPath == '') {
			throw new Exception(
				'Please define the path to the XML file to use via ' .
				'$this->setXmlPath().'
			);
		}

		$formCreator = t3lib_div::makeInstance('tx_ameosformidable');
		$formCreator->init(
			$this,
			t3lib_extMgm::extPath($this->extKey) . $this->xmlPath,
			($this->getObjectUid() > 0) ? $this->getObjectUid() : false
		);

		return $formCreator;
	}

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

	/**
	 * Returns a form value from the FORMidable object.
	 *
	 * Note: In test mode, this function will return faked values.
	 *
	 * @param string column name of the SEMINARS_TABLE_SEMINARS table as key,
	 *               must not be empty
	 *
	 * @return string form value or an empty string if the value does not exist
	 */
	public function getFormValue($key) {
		$dataSource = ($this->isTestMode)
			? $this->fakedFormValues
			: $this->getFormCreator()->oDataHandler->__aFormData;

		return isset($dataSource[$key]) ? $dataSource[$key] : '';
	}

	/**
	 * Fakes a form data value that is usually provided by the FORMidable
	 * object.
	 *
	 * This function is for testing purposes.
	 *
	 * @param string column name of the SEMINARS_TABLE_SEMINARS table as key,
	 *               must not be empty
	 * @param mixed faked value
	 */
	public function setFakedFormValue($key, $value) {
		$this->fakedFormValues[$key] = $value;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndEditor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndEditor.php']);
}
?>