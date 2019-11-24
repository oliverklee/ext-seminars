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
     * @var string the comma-separated names of other DB tables which we need for JOINs
     */
    protected $additionalTableNames = '';

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
     * @var bool whether $this->count has been calculated
     */
    private $hasCount = false;

    /**
     * @var int how many objects this bag contains
     */
    private $count = 0;

    /**
     * @var bool whether $this->$countWithoutLimit has been calculated
     */
    private $hasCountWithoutLimit = false;

    /**
     * @var int how many objects this bag would hold without the LIMIT
     */
    private $countWithoutLimit = 0;

    /**
     * @var bool whether this bag is at the first element
     */
    private $isRewound = false;

    /**
     * @var \mysqli_result|bool an SQL query result (not converted to an associative array yet)
     */
    protected $dbResult = false;

    /**
     * @var AbstractModel|null
     */
    protected $currentItem = null;

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
        $this->queryParameters = \trim($queryParameters);
        $this->additionalTableNames = !empty($additionalTableNames) ? ', ' . $additionalTableNames : '';
        $this->createEnabledFieldsQuery($showHiddenRecords);

        $this->orderBy = $orderBy;
        $this->groupBy = $groupBy;
        $this->limit = $limit;

        $this->rewind();
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
        $allTableNames = GeneralUtility::trimExplode(
            ',',
            static::$tableName . $this->additionalTableNames
        );
        $this->enabledFieldsQuery = '';

        foreach ($allTableNames as $currentTableName) {
            // Is there a TCA entry for that table?
            $ctrl = $GLOBALS['TCA'][$currentTableName]['ctrl'];
            if (\is_array($ctrl)) {
                $this->enabledFieldsQuery .= \Tx_Oelib_Db::enableFields($currentTableName, $showHiddenRecords);
            }
        }
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
        if ($this->isRewound) {
            return;
        }

        // frees old results if there are any
        if ($this->dbResult) {
            $GLOBALS['TYPO3_DB']->sql_free_result($this->dbResult);
            // We don't need to null out $this->dbResult as it will be overwritten immediately anyway.
        }

        $this->dbResult = \Tx_Oelib_Db::select(
            static::$tableName . '.*',
            static::$tableName . $this->additionalTableNames,
            $this->queryParameters . $this->enabledFieldsQuery,
            $this->groupBy,
            $this->orderBy,
            $this->limit
        );

        $this->createItemFromDbResult();

        $this->isRewound = true;
    }

    /**
     * Advances to the next record and returns a reference to that object.
     *
     * @return AbstractModel|null
     */
    public function next()
    {
        if (!$this->dbResult) {
            $this->currentItem = null;
            return null;
        }

        $this->createItemFromDbResult();
        $this->isRewound = false;

        return $this->current();
    }

    /**
     * Creates the current item in $this->currentItem, using $this->dbResult
     * as a source. If the current item cannot be created, $this->currentItem
     * will be nulled out.
     *
     * $this->dbResult must be ensured to be not false when this function is
     * called.
     *
     * @return void
     */
    protected function createItemFromDbResult()
    {
        $this->currentItem = GeneralUtility::makeInstance(static::$modelClassName, 0, $this->dbResult);
        $this->valid();
    }

    /**
     * Returns the current object (which may be NULL).
     *
     * @return AbstractModel|null
     */
    public function current()
    {
        return $this->currentItem;
    }

    /**
     * Checks isOk() and, in case of failure (e.g., there is no more data
     * from the DB), nulls out $this->currentItem.
     *
     * If the function isOk() returns TRUE, nothing is changed.
     *
     * @return bool whether the current item is valid
     */
    public function valid(): bool
    {
        if (empty($this->currentItem) || !$this->currentItem->comesFromDatabase()) {
            $this->currentItem = null;
            return false;
        }

        return true;
    }

    /**
     * Returns the UID of the current item.
     *
     * @return int the UID of the current item, will be > 0
     */
    public function key(): int
    {
        if (!$this->valid()) {
            throw new \RuntimeException('The current item is not valid.', 1333292257);
        }

        return $this->current()->getUid();
    }

    /**
     * Retrieves the number of objects this bag contains.
     *
     * Note: This function might rewind().
     *
     * @return int the total number of objects in this bag, may be zero
     */
    public function count(): int
    {
        if ($this->hasCount) {
            return $this->count;
        }
        if ($this->isEmpty()) {
            return 0;
        }

        $this->count = $GLOBALS['TYPO3_DB']->sql_num_rows($this->dbResult);
        $this->hasCount = true;

        return $this->count;
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

        $dbResultRow = \Tx_Oelib_Db::selectSingle(
            'COUNT(*) AS number ',
            static::$tableName . $this->additionalTableNames,
            $this->queryParameters . $this->enabledFieldsQuery
        );

        $this->countWithoutLimit = (int)$dbResultRow['number'];
        $this->hasCountWithoutLimit = true;

        return $this->countWithoutLimit;
    }

    /**
     * Checks whether this bag is empty.
     *
     * Note: This function might rewind().
     *
     * @return bool TRUE if this bag is empty, FALSE otherwise
     */
    public function isEmpty(): bool
    {
        if ($this->hasCount) {
            return $this->count === 0;
        }

        $this->rewind();
        $isEmpty = !\is_object($this->current());
        if ($isEmpty) {
            $this->count = 0;
            $this->hasCount = true;
        }

        return $isEmpty;
    }

    /**
     * Gets a comma-separated, sorted list of UIDs of the records in this bag.
     *
     * This function will leave the iterator pointing to after the last element.
     *
     * @return string comma-separated, sorted list of UIDs of the records in
     *                this bag, will be an empty string if this bag is empty
     */
    public function getUids(): string
    {
        $uids = [];

        /** @var AbstractModel $currentItem */
        foreach ($this as $currentItem) {
            $uids[] = $currentItem->getUid();
        }

        sort($uids, SORT_NUMERIC);

        return implode(',', $uids);
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
