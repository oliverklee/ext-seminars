<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopicInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversNothing
 */
final class EventTopicInterfaceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function extendsEventInterface(): void
    {
        $subject = $this->createStub(EventTopicInterface::class);

        self::assertInstanceOf(EventInterface::class, $subject);
    }
}
