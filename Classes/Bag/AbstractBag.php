<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Bag;

use OliverKlee\Seminars\OldModel\AbstractModel;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This aggregate class holds a bunch of objects that are created from
 * the result of an SQL query and allows to iterate over them.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class AbstractBag implements \Iterator, \Tx_Oelib_Interface_ConfigurationCheckable
{
    /**
     * @var string
     */
    protected static $modelClassName = '';

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = '';

    /**
     * comma-separated list of table names
     *
     * @var string
     */
    private $allTableNames = '';

    /**
     * @var string the ORDER BY clause (without the actual string "ORDER BY")
     */
    private $orderBy = '';

    /**
     * @var string the GROUP BY clause (without the actual string "GROUP BY")
     */
    private $groupBy = '';

    /**
     * @var string the LIMIT clause (without the actual string "LIMIT")
     */
    private $limit = '';

    /**
     * @var string will be prepended to the WHERE clause using AND, e.g. 'pid=42'
     *             (the AND and the enclosing spaces are not necessary for this
     *             parameter)
     */
    private $queryParameters = '';

    /**
     * @var string will be prepended to the WHERE clause, making sure that only
     *             enabled and non-deleted records will be processed
     */
    private $enabledFieldsQuery = '';

    /**
     * @var bool
     */
    private $queryHasBeenExecuted = false;

    /**
     * @var array[]
     */
    private $queryResult = [];

    /**
     * @var int
     */
    private $key = 0;

    /**
     * @var int how many objects this bag would hold without the LIMIT
     */
    private $countWithoutLimit = 0;

    /**
     * @var bool whether $this->$countWithoutLimit has been calculated
     */
    private $hasCountWithoutLimit = false;

    /**
     * Creates a bag that contains test records and allows to iterate over them.
     *
     * @param string $queryParameters
     *        string that will be prepended to the WHERE clause using AND, e.g. 'pid=42'
     *        (the AND and the enclosing spaces are not necessary for this parameter)
     * @param string $additionalTableNames
     *        comma-separated names of additional DB tables used for JOINs, may be empty
     * @param string $groupBy
     *        GROUP BY clause (may be empty), must already be safeguarded against SQL injection
     * @param string $orderBy
     *        ORDER BY clause (may be empty), must already be safeguarded against SQL injection
     * @param string $limit
     *        LIMIT clause (may be empty), must already be safeguarded against SQL injection
     * @param int $showHiddenRecords
     *        If $showHiddenRecords is set (0/1), any hidden fields in records are ignored.
     */
    public function __construct(
        string $queryParameters = '1=1',
        string $additionalTableNames = '',
        string $groupBy = '',
        string $orderBy = 'uid',
        $limit = '',
        int $showHiddenRecords = -1
    ) {
        $this->allTableNames = static::$tableName
            . (!empty($additionalTableNames) ? ', ' . $additionalTableNames : '');
        $this->queryParameters = \trim($queryParameters);
        $this->createEnabledFieldsQuery($showHiddenRecords);

        $this->orderBy = $orderBy;
        $this->groupBy = $groupBy;
        $this->limit = $limit;
    }

    /**
     * For the main DB table and the additional tables, writes the corresponding
     * concatenated output from \Tx_Oelib_Db::enableFields into
     * $this->enabledFieldsQuery.
     *
     * @param int $showHiddenRecords If $showHiddenRecords is set (0/1), any hidden-fields in records are ignored.
     *
     * @return void
     */
    private function createEnabledFieldsQuery(int $showHiddenRecords = -1)
    {
        $query = '';
        foreach (GeneralUtility::trimExplode(',', $this->allTableNames, true) as $table) {
            if (isset($GLOBALS['TCA'][$table])) {
                $query .= \Tx_Oelib_Db::enableFields($table, $showHiddenRecords);
            }
        }

        $this->enabledFieldsQuery = $query;
    }

    /**
     * Sets the iterator to the first object, using additional
     * query parameters from $this->queryParameters for the DB query.
     * The query works so that the column names are *not*
     * prefixed with the table name.
     *
     * @return void
     */
    public function rewind()
    {
        $this->key = 0;
    }

    /**
     * @return void
     */
    private function executeQueryIfNotDoneYet()
    {
        if ($this->queryHasBeenExecuted) {
            return;
        }

        $this->queryResult = \Tx_Oelib_Db::selectMultiple(
            static::$tableName . '.*',
            $this->allTableNames,
            $this->queryParameters . $this->enabledFieldsQuery,
            $this->groupBy,
            $this->orderBy,
            $this->limit
        );
        $this->queryHasBeenExecuted = true;
    }

    /**
     * Advances to the next record and returns a reference to that object.
     *
     * @return AbstractModel|null
     */
    public function next()
    {
        $this->key++;

        return $this->current();
    }

    /**
     * Returns the current object (which may be NULL).
     *
     * @return AbstractModel|null
     */
    public function current()
    {
        if (!$this->valid()) {
            return null;
        }

        $data = $this->queryResult[$this->key()];

        return static::$modelClassName::fromData($data);
    }

    /**
     * @return bool whether the current item is valid
     */
    public function valid(): bool
    {
        $this->executeQueryIfNotDoneYet();

        return isset($this->queryResult[$this->key()]);
    }

    /**
     * Returns the key of the current item (not the UID).
     *
     * @return int|null
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Retrieves the number of objects this bag contains.
     *
     * @return int the total number of objects in this bag, may be zero
     */
    public function count(): int
    {
        $this->executeQueryIfNotDoneYet();

        return \count($this->queryResult);
    }

    /**
     * Retrieves the number of objects this bag would hold if the LIMIT part of
     * the query would not have been used.
     *
     * @return int the total number of objects in this bag without any
     *                 limit, may be zero
     */
    public function countWithoutLimit(): int
    {
        if ($this->hasCountWithoutLimit) {
            return $this->countWithoutLimit;
        }

        $count = \Tx_Oelib_Db::count($this->allTableNames, $this->queryParameters . $this->enabledFieldsQuery);
        $this->countWithoutLimit = $count;
        $this->hasCountWithoutLimit = true;

        return $this->countWithoutLimit;
    }

    /**
     * Checks whether this bag is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Gets a comma-separated, sorted list of UIDs of the records in this bag.
     *
     * This function will leave the iterator pointing to after the last element.
     *
     * @return string comma-separated, sorted list of UIDs of the records in this bag
     */
    public function getUids(): string
    {
        $this->executeQueryIfNotDoneYet();

        $uids = [];
        /** @var AbstractModel $currentItem */
        foreach ($this as $currentItem) {
            $uids[] = $currentItem->getUid();
        }
        \sort($uids, SORT_NUMERIC);

        return \implode(',', $uids);
    }

    /**
     * Checks whether the current item is okay and returns its error messages
     * from the configuration check.
     *
     * @return string error messages from the configuration check, may be empty
     */
    public function checkConfiguration(): string
    {
        if ($this->current() !== null && $this->current()->comesFromDatabase()) {
            return $this->current()->checkConfiguration(true);
        }

        return '';
    }

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace(): string
    {
        return 'plugin.tx_seminars.';
    }
}
