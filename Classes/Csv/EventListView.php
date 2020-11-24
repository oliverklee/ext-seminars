<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a CSV export of events.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Csv_EventListView extends \Tx_Seminars_Csv_AbstractListView
{
    /**
     * @var string
     */
    protected $tableName = 'tx_seminars_seminars';

    /**
     * @var \Tx_Oelib_Configuration
     */
    protected $configuration = null;

    /**
     * Sets the page UID of the records to retrieve.
     *
     * @param int $pageUid the page UID of the records, must be > 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setPageUid(int $pageUid)
    {
        if ($pageUid <= 0) {
            throw new \InvalidArgumentException('$pageUid must be > 0, but actually is: ' . $pageUid, 1390329634);
        }

        $this->pageUid = $pageUid;
    }

    /**
     * Returns the keys of the fields to export.
     *
     * @return string[]
     */
    protected function getFieldKeys(): array
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromEventsForCsv');
    }

    /**
     * Renders this CSV list.
     *
     * @return string
     */
    public function render(): string
    {
        if (!$this->hasPageUid()) {
            return '';
        }

        $allLines = array_merge([$this->createCsvHeading()], $this->createCsvBodyLines());

        return $this->createCsvSeparatorLine() . implode(self::LINE_SEPARATOR, $allLines) . self::LINE_SEPARATOR;
    }

    /**
     * Returns the localized field names.
     *
     * @return string[] the translated field names in an array, will be empty if no fields should be exported
     */
    protected function getLocalizedCsvHeadings(): array
    {
        $translations = [];
        $translator = $this->getInitializedTranslator();

        foreach ($this->getFieldKeys() as $fieldName) {
            $translations[] = rtrim($translator->getLL($this->getTableName() . '.' . $fieldName), ':');
        }

        return $translations;
    }

    /**
     * Creates the body lines of the CSV export.
     *
     * @return string[]
     */
    protected function createCsvBodyLines(): array
    {
        /** @var \Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Event::class);
        $builder->setBackEndMode();
        $builder->setSourcePages((string)$this->getPageUid(), self::RECURSION_DEPTH);

        $csvLines = [];
        /** @var \Tx_Seminars_OldModel_Event $event */
        foreach ($builder->build() as $event) {
            $csvLines[] = implode(self::COLUMN_SEPARATOR, $this->createCsvColumnsForEvent($event));
        }

        return $csvLines;
    }

    /**
     * Retrieves data from an object and returns that data as an array of values. The individual values are already wrapped in
     * double quotes, with the contents having all quotes escaped.
     *
     * @param \Tx_Seminars_OldModel_Event $event object that will deliver the data
     *
     * @return string[] the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForEvent(\Tx_Seminars_OldModel_Event $event): array
    {
        $csvLines = [];

        foreach ($this->getFieldKeys() as $key) {
            $csvLines[] = $this->escapeFieldForCsv($event->getEventData($key));
        }

        return $csvLines;
    }
}
