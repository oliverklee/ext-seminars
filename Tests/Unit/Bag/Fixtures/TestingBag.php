<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Bag\Fixtures;

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
    protected static string $modelClassName = TestingModel::class;

    /**
     * @var non-empty-string
     */
    protected static string $tableName = 'tx_seminars_test';
}
