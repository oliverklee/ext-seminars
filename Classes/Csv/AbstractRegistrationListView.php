<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Seminars\Bag\RegistrationBag;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a CSV export of registrations.
 */
abstract class AbstractRegistrationListView extends AbstractListView
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
     * @throws \InvalidArgumentException
     */
    public function setPageUid(int $pageUid): void
    {
        if ($pageUid < 0) {
            throw new \InvalidArgumentException('$pageUid must be >= 0, but actually is: ' . $pageUid, 1390307753);
        }

        $this->pageUid = $pageUid;
    }

    /**
     * Sets the event UID of the registrations to retrieve.
     *
     * @param int $eventUid the event UID of the registrations, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setEventUid(int $eventUid): void
    {
        if ($eventUid < 0) {
            throw new \InvalidArgumentException('$eventUid must be >= 0, but actually is: ' . $eventUid, 1390320633);
        }

        $this->eventUid = $eventUid;
    }

    /**
     * Returns the event UID of the registrationsToRetrieve.
     *
     * @return int the event UID, will be >= 0
     */
    protected function getEventUid(): int
    {
        return $this->eventUid;
    }

    /**
     * Checks whether a non-zero event UID has been set.
     */
    protected function hasEventUid(): bool
    {
        return $this->getEventUid() > 0;
    }

    /**
     * @throws \BadMethodCallException
     */
    public function render(): string
    {
        if (!$this->hasPageUid() && !$this->hasEventUid()) {
            throw new \BadMethodCallException(
                'render() must only be called after either a page UID or an event has been set.',
                1390320210
            );
        }
        if ($this->hasPageUid() && $this->hasEventUid()) {
            throw new \BadMethodCallException(
                'render() must only be called after either a page UID or an event has been set, but not both.',
                1390329291
            );
        }

        $allLines = array_merge([$this->createCsvHeading()], $this->createCsvBodyLines());

        $allLines = $this->createCsvSeparatorLine()
            . implode(self::LINE_SEPARATOR, $allLines)
            . self::LINE_SEPARATOR;

        /** @var HookProvider $csvHookProvider */
        $csvHookProvider = GeneralUtility::makeInstance(HookProvider::class, RegistrationListCsv::class);
        return $csvHookProvider->executeHookReturningModifiedValue('modifyCsv', $allLines, $this);
    }

    /**
     * Returns the localized field names.
     *
     * @return string[] the translated field names in an array, will be empty if no fields should be exported
     */
    protected function getLocalizedCsvHeadings(): array
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
    protected function createLocalizedCsvHeadingsForOneTable(array $fieldNames, string $localizationPrefix): array
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
     * @return array<int, non-empty-string>
     */
    abstract protected function getFrontEndUserFieldKeys(): array;

    /**
     * Returns the keys of the registration fields to export.
     *
     * @return array<int, non-empty-string>
     */
    abstract protected function getRegistrationFieldKeys(): array;

    /**
     * Creates the body lines of the CSV export.
     *
     * @return string[]
     */
    protected function createCsvBodyLines(): array
    {
        $registrationBagBuilder = $this->createRegistrationBagBuilder();

        if ($this->hasEventUid()) {
            $registrationBagBuilder->limitToEvent($this->getEventUid());
        } elseif ($this->hasPageUid()) {
            $registrationBagBuilder->setSourcePages((string)$this->getPageUid(), self::RECURSION_DEPTH);
        }

        return $this->getRegistrationsCsvList($registrationBagBuilder);
    }

    /**
     * Creates a registrationBagBuilder with some preset limitations.
     *
     * @return RegistrationBagBuilder the bag builder with some preset limitations
     */
    protected function createRegistrationBagBuilder(): RegistrationBagBuilder
    {
        /** @var RegistrationBagBuilder $registrationBagBuilder */
        $registrationBagBuilder = GeneralUtility::makeInstance(RegistrationBagBuilder::class);

        if (!$this->shouldAlsoContainRegistrationsOnQueue()) {
            $registrationBagBuilder->limitToRegular();
        }

        $registrationBagBuilder->limitToExistingUsers();

        return $registrationBagBuilder;
    }

    /**
     * Checks whether the export should also contain registrations that are on the queue.
     */
    abstract protected function shouldAlsoContainRegistrationsOnQueue(): bool;

    /**
     * Returns the list of registrations as CSV separated values.
     *
     * The fields are separated by semicolons and the lines by CRLF.
     *
     * @param RegistrationBagBuilder $builder the bag builder already limited to the registrations
     *        which should be returned
     *
     * @return string[] the list of registrations, will be empty if no registrations have been given
     *
     * @throws \RuntimeException
     */
    protected function getRegistrationsCsvList(RegistrationBagBuilder $builder): array
    {
        $csvLines = [];
        /** @var RegistrationBag $bag */
        $bag = $builder->build();

        /** @var \Tx_Seminars_OldModel_Registration $registration */
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
     * @param \Tx_Seminars_OldModel_Registration $model object that will deliver the data
     *
     * @return string[] the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForRegistration(\Tx_Seminars_OldModel_Registration $model): array
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
     * @param \Tx_Seminars_OldModel_Registration $model object that will deliver the data
     *
     * @return string[] the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForFrontEndUser(\Tx_Seminars_OldModel_Registration $model): array
    {
        $csvLines = [];

        foreach ($this->getFrontEndUserFieldKeys() as $key) {
            $csvLines[] = $this->escapeFieldForCsv($model->getUserData($key));
        }

        return $csvLines;
    }
}
