<?php

declare(strict_types=1);

/**
 * This class represents a test object from the database.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing extends \Tx_Seminars_OldModel_Abstract
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected $tableName = 'tx_seminars_test';

    /**
     * Sets the test field of this record to a boolean value.
     *
     * @param bool $test the boolean value to set
     *
     * @return void
     */
    public function setBooleanTest(bool $test)
    {
        $this->setRecordPropertyBoolean('test', $test);
    }

    /**
     * Returns TRUE if the test field of this record is set, FALSE otherwise.
     *
     * @return bool TRUE if the test field of this record is set, FALSE
     *                 otherwise
     */
    public function getBooleanTest(): bool
    {
        return $this->getRecordPropertyBoolean('test');
    }

    /**
     * Adds m:n records that are referenced by this record.
     *
     * Before this function may be called, $this->recordData['uid'] must be set
     * correctly.
     *
     * @param string $mmTable the name of the m:n table, having the fields uid_local, uid_foreign and sorting, must not be empty
     * @param int[] $references array of uids of records from the foreign table to which we should create references, may be empty
     *
     * @return int the number of created m:n records
     */
    public function createMmRecords(string $mmTable, array $references): int
    {
        return parent::createMmRecords($mmTable, $references);
    }
}
