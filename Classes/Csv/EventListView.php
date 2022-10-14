<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Seminars\BagBuilder\EventBagBuilder;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a CSV export of events.
 */
class EventListView extends AbstractListView
{
    /**
     * @var string
     */
    protected $tableName = 'tx_seminars_seminars';

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * Sets the page UID of the records to retrieve.
     *
     * @param int $pageUid the page UID of the records, must be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function setPageUid(int $pageUid): void
    {
        if ($pageUid <= 0) {
            throw new \InvalidArgumentException('$pageUid must be > 0, but actually is: ' . $pageUid, 1390329634);
        }

        $this->pageUid = $pageUid;
    }

    /**
     * Returns the keys of the fields to export.
     *
     * @return array<int, string>
     */
    protected function getFieldKeys(): array
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromEventsForCsv');
    }

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
        $builder = GeneralUtility::makeInstance(EventBagBuilder::class);
        $builder->setBackEndMode();
        $builder->setSourcePages((string)$this->getPageUid(), self::RECURSION_DEPTH);

        $csvLines = [];
        foreach ($builder->build() as $event) {
            $csvLines[] = implode(self::COLUMN_SEPARATOR, $this->createCsvColumnsForEvent($event));
        }

        return $csvLines;
    }

    /**
     * Retrieves data from an object and returns that data as an array of values. The individual values are already wrapped in
     * double quotes, with the contents having all quotes escaped.
     *
     * @param LegacyEvent $event object that will deliver the data
     *
     * @return array<int, string> the data for the keys provided in $keys (may be empty)
     */
    protected function createCsvColumnsForEvent(LegacyEvent $event): array
    {
        $csvLines = [];

        foreach ($this->getFieldKeys() as $key) {
            $csvLines[] = $this->escapeFieldForCsv($event->getEventData($key));
        }

        return $csvLines;
    }
}
