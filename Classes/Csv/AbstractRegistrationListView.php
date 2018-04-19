<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a CSV export of registrations.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Seminars_Csv_AbstractRegistrationListView extends Tx_Seminars_Csv_AbstractListView
{
    /**
     * @var string
     */
    protected $tableName = 'tx_seminars_attendances';

    /**
     * @var int
     */
    protected $eventUid = 0;

    /**
     * Sets the page UID of the records to retrieve.
     *
     * @param int $pageUid the page UID of the records, must be >= 0
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function setPageUid($pageUid)
    {
        if ($pageUid < 0) {
            throw new InvalidArgumentException('$pageUid must be >= 0, but actually is: ' . $pageUid, 1390307753);
        }

        $this->pageUid = $pageUid;
    }

    /**
     * Sets the event UID of the registrations to retrieve.
     *
     * @param int $eventUid the event UID of the registrations, must be >= 0
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function setEventUid($eventUid)
    {
        if ($eventUid < 0) {
            throw new InvalidArgumentException('$eventUid must be >= 0, but actually is: ' . $eventUid, 1390320633);
        }

        $this->eventUid = $eventUid;
    }

    /**
     * Returns the event UID of the registrationsToRetrieve.
     *
     * @return int the event UID, will be >= 0
     */
    protected function getEventUid()
    {
        return $this->eventUid;
    }

    /**
     * Checks whether a non-zero event UID has been set.
     *
     * @return bool
     */
    protected function hasEventUid()
    {
        return $this->getEventUid() > 0;
    }

    /**
     * Renders this CSV list.
     *
     * @return string
     *
     * @throws BadMethodCallException
     */
    public function render()
    {
        if (!$this->hasPageUid() && !$this->hasEventUid()) {
            throw new BadMethodCallException(
                'render() must only be called after either a page UID or an event has been set.',
                1390320210
            );
        }
        if ($this->hasPageUid() && $this->hasEventUid()) {
            throw new BadMethodCallException(
                'render() must only be called after either a page UID or an event has been set, but not both.',
                1390329291
            );
        }

        $allLines = array_merge([$this->createCsvHeading()], $this->createCsvBodyLines());

        return $this->createCsvSeparatorLine() . implode(self::LINE_SEPARATOR, $allLines) . self::LINE_SEPARATOR;
    }

    /**
     * Returns the localized field names.
     *
     * @return string[] the translated field names in an array, will be empty if no fields should be exported
     */
    protected function getLocalizedCsvHeadings()
    {
        $fieldsFromFeUser = $this->createLocalizedCsvHeadingsForOneTable($this->getFrontEndUserFieldKeys(), 'LGL');
        $fieldsFromAttendances = $this->createLocalizedCsvHeadingsForOneTable(
            $this->getRegistrationFieldKeys(),
            $this->getTableName()
        );

        return array_merge($fieldsFromFeUser, $fieldsFromAttendances);
    }

    /**
     * Returns the localized field names.
     *
     * @param string[] $fieldNames the field names to translate, may be empty
     * @param string $localizationPrefix the table to which the fields belong to
     *
     * @return string[] the translated field names in an array, will be empty if no field names were given
     */
    protected function createLocalizedCsvHeadingsForOneTable(array $fieldNames, $localizationPrefix)
    {
        $translations = [];
        $translator = $this->getInitializedTranslator();

        foreach ($fieldNames as $fieldName) {
            $translations[] = rtrim($translator->getLL($localizationPrefix . '.' . $fieldName), ':');
        }

        return $translations;
    }

    /**
     * Returns the keys of the front-end user fields to export.
     *
     * @return string[]
     */
    abstract protected function getFrontEndUserFieldKeys();

    /**
     * Returns the keys of the registration fields to export.
     *
     * @return string[]
     */
    abstract protected function getRegistrationFieldKeys();

    /**
     * Creates the body lines of the CSV export.
     *
     * @return string[]
     */
    protected function createCsvBodyLines()
    {
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
     * @return Tx_Seminars_BagBuilder_Registration the bag builder with some preset limitations
     */
    protected function createRegistrationBagBuilder()
    {
        /** @var Tx_Seminars_BagBuilder_Registration $registrationBagBuilder */
        $registrationBagBuilder = GeneralUtility::makeInstance(Tx_Seminars_BagBuilder_Registration::class);

        if (!$this->shouldAlsoContainRegistrationsOnQueue()) {
            $registrationBagBuilder->limitToRegular();
        }

        $registrationBagBuilder->limitToExistingUsers();

        return $registrationBagBuilder;
    }

    /**
     * Checks whether the export should also contain registrations that are on the queue.
     *
     * @return bool
     */
    abstract protected function shouldAlsoContainRegistrationsOnQueue();

    /**
     * Returns the list of registrations as CSV separated values.
     *
     * The fields are separated by semicolons and the lines by CRLF.
     *
     * @param Tx_Seminars_BagBuilder_Registration $builder
     *        the bag builder already limited to the registrations which should be returned
     *
     * @return string[] the list of registrations, will be empty if no registrations have been given
     *
     * @throws RuntimeException
     */
    protected function getRegistrationsCsvList(Tx_Seminars_BagBuilder_Registration $builder)
    {
        $csvLines = [];
        /** @var $bag Tx_Seminars_Bag_Registration */
        $bag = $builder->build();

        /** @var Tx_Seminars_OldModel_Registration $registration */
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
     * @param Tx_Seminars_OldModel_Registration $model object that will deliver the data
     *
     * @return string[] the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForRegistration(Tx_Seminars_OldModel_Registration $model)
    {
        $csvLines = [];

        foreach ($this->getRegistrationFieldKeys() as $key) {
            $csvLines[] = $this->escapeFieldForCsv($model->getRegistrationData($key));
        }

        return $csvLines;
    }

    /**
     * Retrieves data from an object and returns that data as an array of values. The individual values are already wrapped in
     * double quotes, with the contents having all quotes escaped.
     *
     * @param Tx_Seminars_OldModel_Registration $model object that will deliver the data
     *
     * @return string[] the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForFrontEndUser(Tx_Seminars_OldModel_Registration $model)
    {
        $csvLines = [];

        foreach ($this->getFrontEndUserFieldKeys() as $key) {
            $csvLines[] = $this->escapeFieldForCsv($model->getUserData($key));
        }

        return $csvLines;
    }
}
