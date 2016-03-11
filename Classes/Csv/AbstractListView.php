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
use TYPO3\CMS\Lang\LanguageService;

/**
 * This class creates a CSV export of registrations.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Seminars_Csv_AbstractListView
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
     * The constructor.
     */
    public function __construct()
    {
        $this->configuration = Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars');
    }

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset($this->configuration, $this->translator);
    }

    /**
     * Loads the language data and returns the corresponding translator instance.
     *
     * @return LanguageService
     */
    protected function getInitializedTranslator()
    {
        if ($this->translator === null) {
            if (isset($GLOBALS['LANG'])) {
                $this->translator = $GLOBALS['LANG'];
            } else {
                $this->translator = GeneralUtility::makeInstance('language');
                if (isset($GLOBALS['BE_USER'])) {
                    $this->translator->init($GLOBALS['BE_USER']->uc['lang']);
                } else {
                    $this->translator->init('default');
                }
            }

            $this->translator->includeLLFile('EXT:lang/locallang_general.xml');
            $this->translator->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xml');
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
     * @throws InvalidArgumentException
     */
    abstract public function setPageUid($pageUid);

    /**
     * Returns the page UID of the records to check.
     *
     * @return int the page UID, will be >= 0
     */
    protected function getPageUid()
    {
        return $this->pageUid;
    }

    /**
     * Checks whether a non-zero page UID has been set.
     *
     * @return bool
     */
    protected function hasPageUid()
    {
        return $this->getPageUid() > 0;
    }

    /**
     * Returns the name of the main table for this CSV export.
     *
     * @return string
     */
    protected function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Renders this CSV list.
     *
     * @return string
     */
    abstract public function render();

    /**
     * Depending on the configuration, either returns the first line containing the specification of the separator character
     * or just an empty string.
     *
     * @return string
     */
    protected function createCsvSeparatorLine()
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
    protected function createCsvHeading()
    {
        return implode(self::COLUMN_SEPARATOR, $this->getLocalizedCsvHeadings());
    }

    /**
     * Returns the localized field names.
     *
     * @return string[] the translated field names in an array, will be empty if no fields should be exported
     */
    abstract protected function getLocalizedCsvHeadings();

    /**
     * Creates the body lines of the CSV export.
     *
     * @return string[]
     */
    abstract protected function createCsvBodyLines();

    /**
     * Escapes a single field for CSV.
     *
     * @param string $fieldContent
     *
     * @return string
     */
    protected function escapeFieldForCsv($fieldContent)
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
