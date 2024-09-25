<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a CSV export of registrations.
 *
 * @internal
 */
abstract class AbstractListView
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

    /**
     * @var int<0, max>
     */
    protected int $pageUid = 0;

    protected ?LanguageService $translator = null;

    /**
     * @var non-empty-string
     */
    protected string $tableName;

    protected Configuration $configuration;

    public function __construct()
    {
        $this->configuration = ConfigurationRegistry::get('plugin.tx_seminars');
    }

    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }

    protected function getBackEndUser(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
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
     * @param int<0, max> $pageUid
     *
     * @throws \InvalidArgumentException
     */
    abstract public function setPageUid(int $pageUid): void;

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

    abstract public function render(): string;

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
     * Returns the localized field names.
     *
     * @return list<string> the translated field names in an array, will be empty if no fields should be exported
     */
    abstract protected function getLocalizedCsvHeadings(): array;

    /**
     * Creates the body lines of the CSV export.
     *
     * @return list<string>
     */
    abstract protected function createCsvBodyLines(): array;

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
}
