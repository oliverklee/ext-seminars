<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Bag\Fixtures;

use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;

/**
 * This aggregate class holds a bunch of test objects and allows iterating over them.
 *
 * @extends AbstractBag<TestingModel>
 */
final class TestingBag extends AbstractBag
{
    /**
     * @var class-string<TestingModel>
     */
    protected static $modelClassName = TestingModel::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_test';
}
