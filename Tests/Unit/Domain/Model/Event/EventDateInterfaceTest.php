<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversNothing
 */
final class EventDateInterfaceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function extendsEventInterface(): void
    {
        $subject = $this->createStub(EventDateInterface::class);

        self::assertInstanceOf(EventInterface::class, $subject);
    }
}
