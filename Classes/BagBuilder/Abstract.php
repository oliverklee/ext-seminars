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

/**
 * This builder class creates customized bag objects.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class Tx_Seminars_BagBuilder_Abstract
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
    protected $whereClauseParts = array();

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
    protected $additionalTableNames = array();

    /**
     * @var bool whether the timing of records should be ignored
     */
    protected $ignoreTimingOfRecords = false;

    /**
     * @var bool whether hidden records should be shown, too
     */
    protected $showHiddenRecords = false;

    /**
     * The constructor. Checks that $this->tableName is not empty.
     */
    public function __construct()
    {
        if ($this->tableName == '') {
            throw new RuntimeException('The attribute $this->tableName must not be empty.', 1333292618);
        }
    }

    /**
     * Creates and returns the customized bag.
     *
     * @return Tx_Seminars_Bag_Abstract customized, newly-created bag
     */
    public function build()
    {
        /** @var Tx_Seminars_Bag_Abstract $bag */
        $bag = GeneralUtility::makeInstance(
            $this->bagClassName,
            $this->getWhereClause(),
            implode(',', $this->additionalTableNames),
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
    public function setSourcePages($sourcePagePids, $recursionDepth = 0)
    {
        if (!preg_match('/^([\d+,] *)*\d+$/', $sourcePagePids)) {
            unset($this->whereClauseParts['pages']);
            return;
        }

        $recursivePidList = Tx_Oelib_Db::createRecursivePageList(
            $sourcePagePids, $recursionDepth
        );

        $this->whereClauseParts['pages'] = $this->tableName . '.pid IN (' .
            $recursivePidList . ')';
    }

    /**
     * Checks whether some source pages have already been set.
     *
     * @return bool TRUE if source pages have already been set, FALSE
     *                 otherwise
     */
    public function hasSourcePages()
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
        $this->whereClauseParts['tests'] = $this->tableName .
            '.is_dummy_record = 1';
    }

    /**
     * Returns the WHERE clause for the bag to create.
     *
     * The WHERE clause will be complete except for the enableFields additions.
     *
     * If the bag does not have any limitations imposed upon, the return value
     * will be a tautology.
     *
     * @return string complete WHERE clause for the bag to create, will
     *                not be empty
     */
    public function getWhereClause()
    {
        if (empty($this->whereClauseParts)) {
            return '1=1';
        }

        return implode(' AND ', $this->whereClauseParts);
    }

    /**
     * Adds the table name given in the parameter $additionalTableName to
     * $this->additionalTableNames.
     *
     * @param string $additionalTableName the table name to add to the additional table names array, must not be empty
     *
     * @return void
     */
    public function addAdditionalTableName($additionalTableName)
    {
        if ($additionalTableName == '') {
            throw new InvalidArgumentException('The parameter $additionalTableName must not be empty.', 1333292599);
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
    public function removeAdditionalTableName($additionalTableName)
    {
        if ($additionalTableName == '') {
            throw new InvalidArgumentException('The parameter $additionalTableName must not be empty.', 1333292576);
        }

        if (!isset($this->additionalTableNames[$additionalTableName])) {
            throw new InvalidArgumentException(
                'The given additional table name does not exist in the list of additional table names.', 1333292582
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
    public function setOrderBy($orderBy)
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
    public function setLimit($limit)
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
}
