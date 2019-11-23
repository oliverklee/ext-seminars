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
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected $tableName = 'tx_seminars_test';

    /**
     * Creates the current item in $this->currentItem, using $this->dbResult
     * as a source. If the current item cannot be created, $this->currentItem
     * will be nulled out.
     *
     * $this->dbResult must be ensured to be non-NULL when this function is called.
     *
     * @return void
     */
    protected function createItemFromDbResult()
    {
        $this->currentItem = new TestingModel(0, $this->dbResult);
        $this->valid();
    }
}
