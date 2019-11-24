<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Bag;

use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;

/**
 * This aggregate class holds a bunch of test objects and allows to iterate over them.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class TestingBag extends AbstractBag
{
    /**
     * @var string
     */
    protected static $modelClassName = TestingModel::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_test';
}
