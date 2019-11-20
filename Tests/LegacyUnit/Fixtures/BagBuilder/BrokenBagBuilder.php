<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\BagBuilder;

use OliverKlee\Seminars\BagBuilder\AbstractBagBuilder;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Bag\TestingBag;

/**
 * This builder class creates customized test bag objects.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
final class BrokenBagBuilder extends AbstractBagBuilder
{
    /**
     * @var string class name of the bag class that will be built
     */
    protected $bagClassName = TestingBag::class;
}
