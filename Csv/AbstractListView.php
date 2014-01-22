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
 * This class creates a CSV export of registrations.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Seminars_Csv_AbstractListView {
	/**
	 * @var string
	 */
	const COLUMN_SEPARATOR = ';';

	/**
	 * @var string
	 */
	const LINE_SEPARATOR = CRLF;

	/**
	 * @var integer the depth of the recursion for the back-end pages
	 */
	const RECURSION_DEPTH = 250;

	/**
	 * @var integer
	 */
	protected $pageUid = 0;

	/**
	 * @var language
	 */
	protected $translator = NULL;

	/**
	 * @var string
	 */
	protected $tableName = '';

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->configuration = Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars');
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->configuration, $this->translator);
	}

	/**
	 * Loads the language data and returns the corresponding translator instance.
	 *
	 * @return language
	 */
	protected function getInitializedTranslator() {
		if ($this->translator === NULL) {
			if (isset($GLOBALS['LANG'])) {
				$this->translator = $GLOBALS['LANG'];
			} else {
				$this->translator = t3lib_div::makeInstance('language');
				if (isset($GLOBALS['BE_USER'])) {
					$this->translator->init($GLOBALS['BE_USER']->uc['lang']);
				} else {
					$this->translator->init('default');
				}
			}

			$this->translator->includeLLFile(t3lib_extMgm::extPath('lang') . 'locallang_general.xml');
			$this->translator->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang_db.xml');
			$this->includeAdditionalLanguageFiles();
		}

		return $this->translator;
	}

	/**
	 * Includes additional language files for $this->translator.
	 *
	 * This function is intended to be overwritten in subclasses.
	 *
	 * @return void
	 */
	protected function includeAdditionalLanguageFiles() {
	}

	/**
	 * Sets the page UID of the records to retrieve.
	 *
	 * @param integer $pageUid the page UID of the records
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	abstract public function setPageUid($pageUid);

	/**
	 * Returns the page UID of the records to check.
	 *
	 * @return integer the page UID, will be >= 0
	 */
	protected function getPageUid() {
		return $this->pageUid;
	}

	/**
	 * Checks whether a non-zero page UID has been set.
	 *
	 * @return boolean
	 */
	protected function hasPageUid() {
		return $this->getPageUid() > 0;
	}

	/**
	 * Returns the name of the main table for this CSV export.
	 *
	 * @return string
	 */
	protected function getTableName() {
		return $this->tableName;
	}

	/**
	 * Renders this CSV list.
	 *
	 * @return string
	 */
	abstract public function render();

	/**
	 * Creates the heading line for a CSV event list.
	 *
	 * @return string header list, will not be empty if the CSV export has been configured correctly
	 */
	protected function createCsvHeading() {
		return implode(self::COLUMN_SEPARATOR, $this->getLocalizedCsvHeadings());
	}

	/**
	 * Returns the localized field names.
	 *
	 * @return array<string> the translated field names in an array, will be empty if no fields should be exported
	 */
	abstract protected function getLocalizedCsvHeadings();

	/**
	 * Creates the body lines of the CSV export.
	 *
	 * @return array<string>
	 */
	abstract protected function createCsvBodyLines();

	/**
	 * Escapes a single field for CSV.
	 *
	 * @param string $fieldContent
	 *
	 * @return string
	 */
	protected function escapeFieldForCsv($fieldContent) {
		if (strpos($fieldContent, '"') !== FALSE) {
			$escapedFieldValue = '"' . str_replace('"', '""', $fieldContent) . '"';
		} elseif ((strpos($fieldContent, ';') !== FALSE) || (strpos($fieldContent, LF) !== FALSE)) {
			$escapedFieldValue = '"' . $fieldContent . '"';
		} else {
			$escapedFieldValue = $fieldContent;
		}

		return $escapedFieldValue;
	}
}