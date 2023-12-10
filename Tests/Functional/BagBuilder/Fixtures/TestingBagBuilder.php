<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BagBuilder\Fixtures;

use OliverKlee\Seminars\BagBuilder\AbstractBagBuilder;
use OliverKlee\Seminars\Tests\Unit\Bag\Fixtures\TestingBag;

/**
 * This builder class creates customized test bag objects.
 *
 * @extends AbstractBagBuilder<TestingBag>
 */
final class TestingBagBuilder extends AbstractBagBuilder
{
    /**
     * @var class-string<TestingBag> class name of the bag class that will be built
     */
    protected $bagClassName = TestingBag::class;

    /**
     * @var string the table name of the bag to build
     */
    protected $tableName = 'tx_seminars_test';

    /**
     * Limits the bag to records with a particular title.
     *
     * @param string $title title which the bag elements must match, may be empty, must already be SQL-safe
     */
    public function limitToTitle(string $title): void
    {
        $this->whereClauseParts['title'] = 'title = "' . $title . '"';
    }

    /**
     * Returns the additional table names.
     *
     * @return string[] the additional table names, may be empty
     */
    public function getAdditionalTableNames(): array
    {
        return $this->additionalTableNames;
    }

    /**
     * Returns the order by statement.
     *
     * @return string the order by statement, may be empty
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * Returns the limit statement.
     *
     * @return string the limit statement, may be empty
     */
    public function getLimit(): string
    {
        return $this->limit;
    }

    /**
     * Sets $this->tableName with the value in the parameter $tableName.
     *
     * @param string $tableName the table name to set, may be empty for testing
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }
}
