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
    protected static $tableName = 'tx_seminars_test';

    /**
     * @var bool whether to call `TemplateHelper::init()` during construction in BE mode
     */
    protected $needsTemplateHelperInitialization = false;

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
