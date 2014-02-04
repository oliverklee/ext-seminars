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
abstract class Tx_Seminars_Csv_AbstractRegistrationListView extends Tx_Seminars_Csv_AbstractListView {
	/**
	 * @var string
	 */
	protected $tableName = 'tx_seminars_attendances';

	/**
	 * @var integer
	 */
	protected $eventUid = 0;

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
		if ($pageUid < 0) {
			throw new InvalidArgumentException('$pageUid must be >= 0, but actually is: ' . $pageUid, 1390307753);
		}

		$this->pageUid = $pageUid;
	}

	/**
	 * Sets the event UID of the registrations to retrieve.
	 *
	 * @param integer $eventUid the event UID of the registrations, must be >= 0
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setEventUid($eventUid) {
		if ($eventUid < 0) {
			throw new InvalidArgumentException('$eventUid must be >= 0, but actually is: ' . $eventUid, 1390320633);
		}

		$this->eventUid = $eventUid;
	}

	/**
	 * Returns the event UID of the registrationsToRetrieve.
	 *
	 * @return integer the event UID, will be >= 0
	 */
	protected function getEventUid() {
		return $this->eventUid;
	}

	/**
	 * Checks whether a non-zero event UID has been set.
	 *
	 * @return boolean
	 */
	protected function hasEventUid() {
		return $this->getEventUid() > 0;
	}

	/**
	 * Renders this CSV list.
	 *
	 * @return string
	 *
	 * @throws BadMethodCallException
	 */
	public function render() {
		if (!$this->hasPageUid() && !$this->hasEventUid()) {
			throw new BadMethodCallException(
				'render() must only be called after either a page UID or an event has been set.', 1390320210
			);
		}
		if ($this->hasPageUid() && $this->hasEventUid()) {
			throw new BadMethodCallException(
				'render() must only be called after either a page UID or an event has been set, but not both.', 1390329291
			);
		}

		$allLines = array_merge(array($this->createCsvHeading()), $this->createCsvBodyLines());

		return $this->createCsvSeparatorLine() . implode(self::LINE_SEPARATOR, $allLines) . self::LINE_SEPARATOR;
	}

	/**
	 * Returns the localized field names.
	 *
	 * @return array<string> the translated field names in an array, will be empty if no fields should be exported
	 */
	protected function getLocalizedCsvHeadings() {
		$fieldsFromFeUser = $this->createLocalizedCsvHeadingsForOneTable($this->getFrontEndUserFieldKeys(), 'LGL');
		$fieldsFromAttendances = $this->createLocalizedCsvHeadingsForOneTable(
			$this->getRegistrationFieldKeys(), $this->getTableName()
		);

		return array_merge($fieldsFromFeUser, $fieldsFromAttendances);
	}

	/**
	 * Returns the localized field names.
	 *
	 * @param array $fieldNames the field names to translate, may be empty
	 * @param string $localizationPrefix the table to which the fields belong to
	 *
	 * @return array the translated field names in an array, will be empty if no field names were given
	 */
	protected function createLocalizedCsvHeadingsForOneTable(array $fieldNames, $localizationPrefix) {
		$translations = array();
		$translator = $this->getInitializedTranslator();

		foreach ($fieldNames as $fieldName) {
			$translations[] = rtrim($translator->getLL($localizationPrefix . '.' . $fieldName), ':');
		}

		return $translations;
	}

	/**
	 * Returns the keys of the front-end user fields to export.
	 *
	 * @return array<string>
	 */
	abstract protected function getFrontEndUserFieldKeys();

	/**
	 * Returns the keys of the registration fields to export.
	 *
	 * @return array<string>
	 */
	abstract protected function getRegistrationFieldKeys();

	/**
	 * Creates the body lines of the CSV export.
	 *
	 * @return array<string>
	 */
	protected function createCsvBodyLines() {
		$registrationBagBuilder = $this->createRegistrationBagBuilder();

		if ($this->hasEventUid()) {
			$registrationBagBuilder->limitToEvent($this->getEventUid());
		} elseif ($this->hasPageUid()) {
			$registrationBagBuilder->setSourcePages($this->getPageUid(), self::RECURSION_DEPTH);
		}

		$csvLines = $this->getRegistrationsCsvList($registrationBagBuilder);

		return $csvLines;
	}

	/**
	 * Creates a registrationBagBuilder with some preset limitations.
	 *
	 * @return tx_seminars_BagBuilder_Registration the bag builder with some preset limitations
	 */
	protected function createRegistrationBagBuilder() {
		/** @var $registrationBagBuilder tx_seminars_BagBuilder_Registration */
		$registrationBagBuilder = t3lib_div::makeInstance('tx_seminars_BagBuilder_Registration');

		if (!$this->shouldAlsoContainRegistrationsOnQueue()) {
			$registrationBagBuilder->limitToRegular();
		}

		$registrationBagBuilder->limitToExistingUsers();

		return $registrationBagBuilder;
	}

	/**
	 * Checks whether the export should also contain registrations that are on the queue.
	 *
	 * @return boolean
	 */
	abstract protected function shouldAlsoContainRegistrationsOnQueue();

	/**
	 * Returns the list of registrations as CSV separated values.
	 *
	 * The fields are separated by semicolons and the lines by CRLF.
	 *
	 * @param tx_seminars_BagBuilder_Registration $builder
	 *        the bag builder already limited to the registrations which should be returned
	 *
	 * @return array<string> the list of registrations, will be empty if no registrations have been given
	 *
	 * @throws RuntimeException
	 */
	protected function getRegistrationsCsvList(tx_seminars_BagBuilder_Registration $builder) {
		$csvLines = array();
		/** @var $bag tx_seminars_Bag_Registration */
		$bag = $builder->build();

		/** @var $registration tx_seminars_registration */
		foreach ($bag as $registration) {
			$userData = $this->createCsvColumnsForFrontEndUser($registration);
			$registrationData = $this->createCsvColumnsForRegistration($registration);
			$csvLines[] = implode(self::COLUMN_SEPARATOR, array_merge($userData, $registrationData));
		}

		return $csvLines;
	}

	/**
	 * Retrieves data from an object and returns that data as an array of values. The individual values are already wrapped in
	 * double quotes, with the contents having all quotes escaped.
	 *
	 * @param tx_seminars_registration $model object that will deliver the data
	 *
	 * @return array<string> the data for the keys provided in $keys (may be empty)
	 */
	protected function createCsvColumnsForRegistration(tx_seminars_registration $model) {
		$csvLines = array();

		foreach ($this->getRegistrationFieldKeys() as $key) {
			$csvLines[] = $this->escapeFieldForCsv($model->getRegistrationData($key));
		}

		return $csvLines;
	}

	/**
	 * Retrieves data from an object and returns that data as an array of values. The individual values are already wrapped in
	 * double quotes, with the contents having all quotes escaped.
	 *
	 * @param tx_seminars_registration $model object that will deliver the data
	 *
	 * @return array<string> the data for the keys provided in $keys (may be empty)
	 */
	protected function createCsvColumnsForFrontEndUser(tx_seminars_registration $model) {
		$csvLines = array();

		foreach ($this->getFrontEndUserFieldKeys() as $key) {
			$csvLines[] = $this->escapeFieldForCsv($model->getUserData($key));
		}

		return $csvLines;
	}
}