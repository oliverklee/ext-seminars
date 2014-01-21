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
class Tx_Seminars_Csv_EventListView extends Tx_Seminars_Csv_AbstractListView {
	/**
	 * @var string
	 */
	protected $tableName = 'tx_seminars_seminars';

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * Sets the page UID of the records to retrieve.
	 *
	 * @param integer $pageUid the page UID of the records, must be > 0
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setPageUid($pageUid) {
		if ($pageUid <= 0) {
			throw new InvalidArgumentException('$pageUid must be > 0, but actually is: ' . $pageUid, 1390329634);
		}

		$this->pageUid = $pageUid;
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
		if (!$this->hasPageUid()) {
			return '';
		}

		$allLines = array_merge(array($this->createCsvHeading()), $this->createCsvBodyLines());

		return implode(self::LINE_SEPARATOR, $allLines) . self::LINE_SEPARATOR;
	}

	/**
	 * Returns the localized field names.
	 *
	 * @return array<string> the translated field names in an array, will be empty if no fields should be exported
	 */
	protected function getLocalizedCsvHeadings() {
		$translations = array();
		$translator = $this->getInitializedTranslator();

		foreach ($this->getFieldKeys() as $fieldName) {
			$translations[] = rtrim($translator->getLL($this->getTableName() . '.' . $fieldName), ':');
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
		$builder->setSourcePages($this->getPageUid(), self::RECURSION_DEPTH);

		$csvLines = array();
		/** @var $seminar tx_seminars_seminar */
		foreach ($builder->build() as $seminar) {
			$csvLines[] = implode(self::COLUMN_SEPARATOR, $this->createCsvColumnsForEvent($seminar));
		}

		return $csvLines;
	}

	/**
	 * Retrieves data from an object and returns that data as an array of values. The individual values are already wrapped in
	 * double quotes, with the contents having all quotes escaped.
	 *
	 * @param tx_seminars_seminar $event object that will deliver the data
	 *
	 * @return array<string> the data for the keys provided in $keys (may be empty)
	 */
	protected function createCsvColumnsForEvent(tx_seminars_seminar $event) {
		$csvLines = array();

		foreach ($this->getFieldKeys() as $key) {
			$csvLines[] = $this->escapeFieldForCsv($event->getEventData($key));
		}

		return $csvLines;
	}
}