<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures;

use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * This class represents a test object from the database.
 */
final class TestingModel extends AbstractModel
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static string $tableName = 'tx_seminars_test';

    /**
     * Sets the test field of this record to a boolean value.
     *
     * @param bool $test the boolean value to set
     */
    public function setBooleanTest(bool $test): void
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
     * Before this function may be called, $this->recordData['uid'] must be set correctly.
     *
     * @param non-empty-string $mmTable the name of the m:n table, having the fields uid_local, uid_foreign and sorting
     * @param array<int> $references UIDs of records from the foreign table to which we should create references
     *
     * @return int<0, max> the number of created m:n records
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function createMmRecords(string $mmTable, array $references): int
    {
        return parent::createMmRecords($mmTable, $references);
    }
}
