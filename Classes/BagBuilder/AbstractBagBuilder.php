<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BagBuilder;

use OliverKlee\Seminars\Bag\AbstractBag;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * This builder class creates customized bag objects.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class AbstractBagBuilder
{
    /**
     * @var string class name of the bag class that will be built
     */
    protected $bagClassName = '';

    /**
     * @var string the table name of the bag to build
     */
    protected $tableName = '';

    /**
     * @var string[] associative array with the WHERE clause parts (will be concatenated with " AND " later)
     */
    protected $whereClauseParts = [];

    /**
     * @var string the sorting field
     */
    protected $orderBy = 'uid';

    /**
     * @var int the field by which the DB query result should be grouped
     */
    protected $groupBy = '';

    /**
     * @var string the number of records to retrieve; leave empty to set
     *             no limit
     */
    protected $limit = '';

    /**
     * @var string[] additional table names for the query
     */
    protected $additionalTableNames = [];

    /**
     * @var bool whether the timing of records should be ignored
     */
    protected $ignoreTimingOfRecords = false;

    /**
     * @var bool whether hidden records should be shown, too
     */
    protected $showHiddenRecords = false;

    /**
     * @var PageRepository
     */
    protected $pageRepository = null;

    /**
     * The constructor. Checks that $this->tableName is not empty.
     */
    public function __construct()
    {
        if ($this->tableName === '') {
            throw new \RuntimeException('The attribute $this->tableName must not be empty.', 1333292618);
        }

        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    /**
     * Creates and returns the customized bag.
     *
     * @return AbstractBag customized, newly-created bag
     */
    public function build(): AbstractBag
    {
        /** @var AbstractBag $bag */
        $bag = GeneralUtility::makeInstance(
            $this->bagClassName,
            $this->getWhereClause(),
            \implode(',', $this->additionalTableNames),
            $this->groupBy,
            $this->orderBy,
            $this->limit,
            ($this->showHiddenRecords ? 1 : -1),
            $this->ignoreTimingOfRecords
        );
        return $bag;
    }

    /**
     * Configures the bag to work like a BE list: It will use the default
     * sorting in the BE, and hidden records will be shown.
     *
     * @return void
     */
    public function setBackEndMode()
    {
        $this->showHiddenRecords();
        $this->ignoreTimingOfRecords = true;
    }

    /**
     * Sets the PIDs of the system folders that contain the records.
     *
     * @param string $sourcePagePids
     *        comma-separated list of PIDs of the system folders with the records;
     *        must not be empty; need not be safeguarded against SQL injection
     * @param int $recursionDepth
     *        recursion depth, must be >= 0
     *
     * @return void
     */
    public function setSourcePages(string $sourcePagePids, int $recursionDepth = 0)
    {
        if (!\preg_match('/^([\\d+,] *)*\\d+$/', $sourcePagePids)) {
            unset($this->whereClauseParts['pages']);
            return;
        }

        $recursivePidList = \Tx_Oelib_Db::createRecursivePageList($sourcePagePids, $recursionDepth);

        $this->whereClauseParts['pages'] = $this->tableName . '.pid IN (' . $recursivePidList . ')';
    }

    /**
     * Checks whether some source pages have already been set.
     *
     * @return bool TRUE if source pages have already been set, FALSE
     *                 otherwise
     */
    public function hasSourcePages(): bool
    {
        return isset($this->whereClauseParts['pages']);
    }

    /**
     * Sets the created bag to only take records into account that have been
     * created with the oelib testing framework.
     *
     * @return void
     */
    public function setTestMode()
    {
        $this->whereClauseParts['tests'] = $this->tableName . '.is_dummy_record = 1';
    }

    /**
     * Returns the WHERE clause for the bag to create.
     *
     * The WHERE clause will be complete except for the enableFields additions.
     *
     * If the bag does not have any limitations imposed upon, the return value will be a tautology.
     *
     * @return string complete WHERE clause for the bag to create, will not be empty
     */
    public function getWhereClause(): string
    {
        if (empty($this->whereClauseParts)) {
            return '1=1';
        }

        return \implode(' AND ', $this->whereClauseParts);
    }

    /**
     * Returns a WHERE clause part for the bag to create.
     *
     * If the bag does not have such limitation imposed upon, the return value will be empty.
     *
     * @param string $key the limitation key to return, must not be empty
     *
     * @return string WHERE clause part for the bag to create, may be empty
     */
    public function getWhereClausePart(string $key): string
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('The parameter $key must not be empty.', 1569331331);
        }

        return $this->whereClauseParts[$key] ?? '';
    }

    /**
     * Sets a WHERE clause part (limitation) for the bag to create.
     *
     * @param string $key the limitation key to return, must not be empty
     * @param string $value the WHERE clause part of a limitation, empty value removes the limitation
     *
     * @return void
     */
    public function setWhereClausePart(string $key, string $value)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('The parameter $key must not be empty.', 1569331332);
        }

        if (empty($value)) {
            unset($this->whereClauseParts[$key]);
            return;
        }

        $this->whereClauseParts[$key] = $value;
    }

    /**
     * Adds the table name given in the parameter $additionalTableName to
     * $this->additionalTableNames.
     *
     * @param string $additionalTableName the table name to add to the additional table names array, must not be empty
     *
     * @return void
     */
    public function addAdditionalTableName(string $additionalTableName)
    {
        if ($additionalTableName === '') {
            throw new \InvalidArgumentException('The parameter $additionalTableName must not be empty.', 1333292599);
        }

        $this->additionalTableNames[$additionalTableName] = $additionalTableName;
    }

    /**
     * Removes the table name given in the parameter $additionalTableName from
     * $this->additionalTableNames.
     *
     * @param string $additionalTableName the table name to remove from the additional table names array, must not be empty
     *
     * @return void
     */
    public function removeAdditionalTableName(string $additionalTableName)
    {
        if ($additionalTableName === '') {
            throw new \InvalidArgumentException('The parameter $additionalTableName must not be empty.', 1333292576);
        }

        if (!isset($this->additionalTableNames[$additionalTableName])) {
            throw new \InvalidArgumentException(
                'The given additional table name does not exist in the list of additional table names.',
                1333292582
            );
        }

        unset($this->additionalTableNames[$additionalTableName]);
    }

    /**
     * Sets the ORDER BY statement for the bag to build.
     *
     * @param string $orderBy the ORDER BY statement to set, may be empty
     *
     * @return void
     */
    public function setOrderBy(string $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * Sets the LIMIT statement of the bag to build.
     *
     * Examples for the parameter:
     * - "0, 10" to limit the bag to 10 records, starting from the first record
     * - "10, 10" to limit the bag to 10 records, starting from the 11th record
     * - "10" to limit the bag to the first 10 records
     *
     * @param string $limit the LIMIT statement to set, may be empty
     *
     * @return void
     */
    public function setLimit(string $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Configures the bag to also contain hidden records.
     *
     * @return void
     */
    public function showHiddenRecords()
    {
        $this->showHiddenRecords = true;
    }

    protected function getConnectionForTable(string $table): Connection
    {
        return $this->getConnectionPool()->getConnectionForTable($table);
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
