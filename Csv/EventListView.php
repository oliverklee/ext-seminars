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
 * This class creates a CSV export of events.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Csv_EventListView  {
	/**
	 * @var string
	 */
	const COLUMN_SEPARATOR = ';';

	/**
	 * @var string
	 */
	const LINE_SEPARATOR = CRLF;

	/**
	 * @var integer
	 */
	protected $pageUid = 0;

	/**
	 * @var string
	 */
	protected $tableName = 'tx_seminars_seminars';

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * @var language
	 */
	protected $translator = NULL;

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
	 * Sets the page UID of the records to retrieve.
	 *
	 * @param integer $pageUid the page UID of the records, must be >= 0
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setPageUid($pageUid) {
		if ($pageUid <= 0) {
			throw new InvalidArgumentException('$pageUid must be > 0, but actually is: ' . $pageUid, 1390307753);
		}

		$this->pageUid = $pageUid;
	}

	/**
	 * Returns the page UID of the records to check.
	 *
	 * @return integer the page UID, will be >= 0
	 */
	protected function getPageUid() {
		return $this->pageUid;
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
		}

		return $this->translator;
	}

	/**
	 * Returns the keys of the fields to export.
	 *
	 * @return array<string>
	 */
	protected function getFieldKeys() {
		return $this->configuration->getAsTrimmedArray('fieldsFromEventsForCsv');
	}

	/**
	 * Renders this CSV list.
	 *
	 * @return string
	 */
	public function render() {
		if ($this->getPageUid() <= 0) {
			return '';
		}

		$allLines = array_merge(array($this->createCsvHeading()), $this->createCsvBodyLines());

		return implode(self::LINE_SEPARATOR, $allLines) . self::LINE_SEPARATOR;
	}

	/**
	 * Creates the heading line for a CSV event list.
	 *
	 * @return string header list, will not be empty if the CSV export has been configured correctly
	 */
	protected function createCsvHeading() {
		return implode(self::COLUMN_SEPARATOR, $this->localizeCsvHeadings());
	}

	/**
	 * Returns the localized field names.
	 *
	 * @return array<string> the translated field names in an array, will be empty if no fields should be exported
	 */
	protected function localizeCsvHeadings() {
		$translations = array();

		foreach ($this->getFieldKeys() as $fieldName) {
			$translations[] = rtrim($this->getInitializedTranslator()->getLL($this->getTableName() . '.' . $fieldName), ':');
		}

		return $translations;
	}

	/**
	 * Creates the body lines of the CSV export.
	 *
	 * @return array<string>
	 */
	protected function createCsvBodyLines() {
		/** @var $builder tx_seminars_BagBuilder_Event */
		$builder = t3lib_div::makeInstance('tx_seminars_BagBuilder_Event');
		$builder->setBackEndMode();
		$builder->setSourcePages($this->pageUid, 255);

		$csvLines = array();
		/** @var $seminar tx_seminars_seminar */
		foreach ($builder->build() as $seminar) {
			$csvLines[] = implode(self::COLUMN_SEPARATOR, $this->createCsvColumnsForModel($seminar));
		}

		return $csvLines;
	}

	/**
	 * Retrieves data from an object and returns that data as an array of values. The individual values are already wrapped in
	 * double quotes, with the contents having all quotes escaped.
	 *
	 * @param tx_seminars_seminar $model object that will deliver the data
	 *
	 * @return array<string> the data for the keys provided in $keys (may be empty)
	 */
	protected function createCsvColumnsForModel(tx_seminars_seminar $model) {
		$csvLines = array();

		foreach ($this->getFieldKeys() as $key) {
			$csvLines[] = $this->escapeFieldForCsv($model->getEventData($key));
		}

		return $csvLines;
	}

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