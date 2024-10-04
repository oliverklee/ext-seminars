<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a CSV export of registrations.
 *
 * @internal
 */
abstract class AbstractRegistrationListView
{
    /**
     * @var non-empty-string
     */
    protected const COLUMN_SEPARATOR = ';';

    /**
     * @var string
     */
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * @var int the depth of the recursion for the back-end pages
     */
    protected const RECURSION_DEPTH = 250;

    protected Configuration $configuration;

    /**
     * @var int<0, max>
     */
    protected int $pageUid = 0;

    protected ?LanguageService $translator = null;

    /**
     * @var non-empty-string
     */
    protected string $tableName = 'tx_seminars_attendances';

    /**
     * @var int<0, max>
     */
    protected int $eventUid = 0;

    public function __construct()
    {
        $this->configuration = ConfigurationRegistry::get('plugin.tx_seminars');
    }

    protected function getLanguageService(): ?LanguageService
    {
        $languageService = $GLOBALS['LANG'] ?? null;
        \assert(($languageService instanceof LanguageService) || $languageService === null);

        return $languageService;
    }

    protected function getBackEndUser(): ?BackendUserAuthentication
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;
        \assert(($backendUser instanceof BackendUserAuthentication) || $backendUser === null);

        return $backendUser;
    }

    /**
     * Loads the language data and returns the corresponding translator instance.
     */
    protected function getInitializedTranslator(): LanguageService
    {
        if ($this->translator instanceof LanguageService) {
            return $this->translator;
        }

        if ($this->getLanguageService() instanceof LanguageService) {
            $languageService = $this->getLanguageService();
        } else {
            $backEndUser = $this->getBackEndUser();
            if ($backEndUser instanceof BackendUserAuthentication) {
                $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)
                    ->createFromUserPreferences($backEndUser);
            } else {
                $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
            }
        }

        $this->translator = $languageService;
        $languageService->includeLLFile('EXT:core/Resources/Private/Language/locallang_general.xlf');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        $this->includeAdditionalLanguageFiles();

        return $languageService;
    }

    /**
     * Includes additional language files for $this->translator.
     *
     * This function is intended to be overwritten in subclasses.
     */
    protected function includeAdditionalLanguageFiles(): void
    {
    }

    /**
     * @return int<0, max>
     */
    protected function getPageUid(): int
    {
        return $this->pageUid;
    }

    /**
     * Checks whether a non-zero page UID has been set.
     */
    protected function hasPageUid(): bool
    {
        return $this->getPageUid() > 0;
    }

    /**
     * Returns the name of the main table for this CSV export.
     *
     * @return non-empty-string
     */
    protected function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Depending on the configuration, either returns the first line containing the specification of the separator character
     * or just an empty string.
     */
    protected function createCsvSeparatorLine(): string
    {
        if (!$this->configuration->getAsBoolean('addExcelSpecificSeparatorLineToCsv')) {
            return '';
        }

        return 'sep=' . self::COLUMN_SEPARATOR . self::LINE_SEPARATOR;
    }

    /**
     * Creates the heading line for a CSV event list.
     *
     * @return string header list, will not be empty if the CSV export has been configured correctly
     */
    protected function createCsvHeading(): string
    {
        return \implode(self::COLUMN_SEPARATOR, $this->getLocalizedCsvHeadings());
    }

    /**
     * Escapes a single field for CSV.
     */
    protected function escapeFieldForCsv(string $fieldContent): string
    {
        if (\str_contains($fieldContent, '"')) {
            $escapedFieldValue = '"' . \str_replace('"', '""', $fieldContent) . '"';
        } elseif (\str_contains($fieldContent, ';') || \str_contains($fieldContent, "\n")) {
            $escapedFieldValue = '"' . $fieldContent . '"';
        } else {
            $escapedFieldValue = $fieldContent;
        }

        return $escapedFieldValue;
    }

    /**
     * Sets the page UID of the records to retrieve.
     *
     * @param int<0, max> $pageUid the page UID of the records, must be >= 0
     */
    public function setPageUid(int $pageUid): void
    {
        $this->pageUid = $pageUid;
    }

    /**
     * Sets the event UID of the registrations to retrieve.
     *
     * @param int<0, max> $eventUid the event UID of the registrations, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setEventUid(int $eventUid): void
    {
        $this->eventUid = $eventUid;
    }

    /**
     * Returns the event UID of the registrationsToRetrieve.
     *
     * @return int<0, max> the event UID, will be >= 0
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
        if (!$this->hasEventUid() && !$this->hasPageUid()) {
            throw new \BadMethodCallException('No event UID or page UID set', 1390320210);
        }

        $allLines = array_merge([$this->createCsvHeading()], $this->createCsvBodyLines());

        $allLines = $this->createCsvSeparatorLine()
            . implode(self::LINE_SEPARATOR, $allLines)
            . self::LINE_SEPARATOR;

        return GeneralUtility::makeInstance(HookProvider::class, RegistrationListCsv::class)
            ->executeHookReturningModifiedValue('modifyCsv', $allLines, $this);
    }

    /**
     * Returns the localized field names.
     *
     * @return list<string> the translated field names in an array, will be empty if no fields should be exported
     */
    protected function getLocalizedCsvHeadings(): array
    {
        $fieldsFromFeUser = $this->createLocalizedCsvHeadingsForOneTable($this->getFrontEndUserFieldKeys(), 'LGL');
        $fieldsFromAttendances = $this->createLocalizedCsvHeadingsForOneTable(
            $this->getRegistrationFieldKeys(),
            $this->getTableName()
        );

        return \array_merge($fieldsFromFeUser, $fieldsFromAttendances);
    }

    /**
     * Returns the localized field names.
     *
     * @param list<string> $fieldNames the field names to translate, may be empty
     * @param non-empty-string $localizationPrefix the table to which the fields belong to
     *
     * @return list<string> the translated field names in an array, will be empty if no field names were given
     */
    protected function createLocalizedCsvHeadingsForOneTable(array $fieldNames, string $localizationPrefix): array
    {
        $translations = [];
        $translator = $this->getInitializedTranslator();

        foreach ($fieldNames as $fieldName) {
            $translations[] = \rtrim($translator->getLL($localizationPrefix . '.' . $fieldName), ':');
        }

        return $translations;
    }

    /**
     * Returns the keys of the front-end user fields to export.
     *
     * @return list<non-empty-string>
     */
    abstract protected function getFrontEndUserFieldKeys(): array;

    /**
     * Returns the keys of the registration fields to export.
     *
     * @return list<non-empty-string>
     */
    abstract protected function getRegistrationFieldKeys(): array;

    /**
     * Creates the body lines of the CSV export.
     *
     * @return list<string>
     */
    protected function createCsvBodyLines(): array
    {
        $registrationBagBuilder = $this->createRegistrationBagBuilder();

        if ($this->hasEventUid()) {
            $eventUid = $this->getEventUid();
            \assert($eventUid > 0);
            $registrationBagBuilder->limitToEvent($eventUid);
        } elseif ($this->hasPageUid()) {
            $registrationBagBuilder->setSourcePages((string)$this->getPageUid(), self::RECURSION_DEPTH);
        }

        return $this->getRegistrationsCsvList($registrationBagBuilder);
    }

    /**
     * Creates a registrationBagBuilder with some preset limitations.
     */
    protected function createRegistrationBagBuilder(): RegistrationBagBuilder
    {
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
     * @return list<string> the list of registrations, will be empty if no registrations have been given
     *
     * @throws \RuntimeException
     */
    protected function getRegistrationsCsvList(RegistrationBagBuilder $builder): array
    {
        $csvLines = [];

        foreach ($builder->build() as $registration) {
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
     * @param LegacyRegistration $model object that will deliver the data
     *
     * @return list<string> the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForRegistration(LegacyRegistration $model): array
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
     * @param LegacyRegistration $model object that will deliver the data
     *
     * @return list<string> the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForFrontEndUser(LegacyRegistration $model): array
    {
        $csvLines = [];

        foreach ($this->getFrontEndUserFieldKeys() as $key) {
            $csvLines[] = $this->escapeFieldForCsv($model->getUserData($key));
        }

        return $csvLines;
    }
}
