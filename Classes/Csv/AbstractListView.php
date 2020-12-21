<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * This class creates a CSV export of registrations.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class AbstractListView
{
    /**
     * @var string
     */
    const COLUMN_SEPARATOR = ';';

    /**
     * @var string
     */
    const LINE_SEPARATOR = CRLF;

    /**
     * @var int the depth of the recursion for the back-end pages
     */
    const RECURSION_DEPTH = 250;

    /**
     * @var int
     */
    protected $pageUid = 0;

    /**
     * @var LanguageService
     */
    protected $translator = null;

    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var \Tx_Oelib_Configuration
     */
    protected $configuration = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->configuration = \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars');
    }

    /**
     * @return LanguageService|null
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'] ?? null;
    }

    /**
     * @return BackendUserAuthentication|null
     */
    protected function getBackEndUser()
    {
        return $GLOBALS['BE_USER'] ?? null;
    }

    /**
     * Loads the language data and returns the corresponding translator instance.
     *
     * @return LanguageService
     */
    protected function getInitializedTranslator(): LanguageService
    {
        if ($this->translator !== null) {
            return $this->translator;
        }

        if ($this->getLanguageService() !== null) {
            $this->translator = $this->getLanguageService();
        } else {
            $this->translator = GeneralUtility::makeInstance(LanguageService::class);
            if ($this->getBackEndUser() !== null) {
                $this->translator->init($this->getBackEndUser()->uc['lang']);
            } else {
                $this->translator->init('default');
            }
        }

        $this->translator->includeLLFile('EXT:lang/Resources/Private/Language/locallang_general.xlf');
        $this->translator->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        $this->includeAdditionalLanguageFiles();

        return $this->translator;
    }

    /**
     * Includes additional language files for $this->translator.
     *
     * This function is intended to be overwritten in subclasses.
     *
     * @return void
     */
    protected function includeAdditionalLanguageFiles()
    {
    }

    /**
     * Sets the page UID of the records to retrieve.
     *
     * @param int $pageUid the page UID of the records
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    abstract public function setPageUid(int $pageUid);

    /**
     * Returns the page UID of the records to check.
     *
     * @return int the page UID, will be >= 0
     */
    protected function getPageUid(): int
    {
        return $this->pageUid;
    }

    /**
     * Checks whether a non-zero page UID has been set.
     *
     * @return bool
     */
    protected function hasPageUid(): bool
    {
        return $this->getPageUid() > 0;
    }

    /**
     * Returns the name of the main table for this CSV export.
     *
     * @return string
     */
    protected function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Renders this CSV list.
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Depending on the configuration, either returns the first line containing the specification of the separator character
     * or just an empty string.
     *
     * @return string
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
        return implode(self::COLUMN_SEPARATOR, $this->getLocalizedCsvHeadings());
    }

    /**
     * Returns the localized field names.
     *
     * @return string[] the translated field names in an array, will be empty if no fields should be exported
     */
    abstract protected function getLocalizedCsvHeadings(): array;

    /**
     * Creates the body lines of the CSV export.
     *
     * @return string[]
     */
    abstract protected function createCsvBodyLines(): array;

    /**
     * Escapes a single field for CSV.
     *
     * @param string $fieldContent
     *
     * @return string
     */
    protected function escapeFieldForCsv(string $fieldContent): string
    {
        if (strpos($fieldContent, '"') !== false) {
            $escapedFieldValue = '"' . str_replace('"', '""', $fieldContent) . '"';
        } elseif ((strpos($fieldContent, ';') !== false) || (strpos($fieldContent, LF) !== false)) {
            $escapedFieldValue = '"' . $fieldContent . '"';
        } else {
            $escapedFieldValue = $fieldContent;
        }

        return $escapedFieldValue;
    }
}
