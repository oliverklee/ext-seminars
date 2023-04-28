<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper for injecting objects into `GeneralUtility::makeInstance` and automatically cleaning them up afterwards.
 *
 * @mixin TestCase
 */
trait MakeInstanceTrait
{
    /**
     * @var list<class-string>
     */
    private $mockClassNames = [];

    /**
     * Adds an instance to the TYPO3 instance FIFO buffer used by `GeneralUtility::makeInstance()`
     * and registers it for purging in `tearDown()`.
     *
     * In case of a failing test or an exception in the test before the instance is taken
     * from the FIFO buffer, the instance would stay in the buffer and make following tests
     * fail. This function adds it to the list of instances to purge in `tearDown()` in addition
     * to `GeneralUtility::addInstance()`.
     *
     * @param class-string $className
     */
    private function addMockedInstance(string $className, object $instance): void
    {
        GeneralUtility::addInstance($className, $instance);
        $this->mockClassNames[] = $className;
    }

    /**
     * Purges possibly leftover instances from the TYPO3 instance FIFO buffer used by
     * `GeneralUtility::makeInstance()`.
     *
     * This method automatically is called after each test. Hence, there is no need to explicitly call it from
     * `tearDown()`.
     *
     * @after
     */
    public function purgeMockedInstances(): void
    {
        foreach ($this->mockClassNames as $className) {
            GeneralUtility::makeInstance($className);
        }

        $this->mockClassNames = [];
    }
}
