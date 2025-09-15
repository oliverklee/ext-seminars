<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversNothing
 */
final class EventInterfaceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function extendsDomainObjectInterface(): void
    {
        $subject = $this->createStub(EventInterface::class);

        self::assertInstanceOf(DomainObjectInterface::class, $subject);
    }
}
