<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder\Fixtures;

use OliverKlee\Seminars\BagBuilder\AbstractBagBuilder;
use OliverKlee\Seminars\Tests\Unit\Bag\Fixtures\TestingBag;

/**
 * This builder class creates customized test bag objects.
 *
 * @extends AbstractBagBuilder<TestingBag>
 */
final class BrokenBagBuilder extends AbstractBagBuilder
{
    /**
     * @var class-string<TestingBag> class name of the bag class that will be built
     */
    protected $bagClassName = TestingBag::class;
}
