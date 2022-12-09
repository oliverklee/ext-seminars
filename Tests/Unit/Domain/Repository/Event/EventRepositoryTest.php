<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Repository\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @covers \OliverKlee\Seminars\Domain\Repository\Event\EventRepository
 */
final class EventRepositoryTest extends UnitTestCase
{
    /**
     * @var EventRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManagerStub = $this->createMock(ObjectManagerInterface::class);
        $this->subject = new EventRepository($objectManagerStub);
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }

    /**
     * @test
     */
    public function isRawDataCapableRepository(): void
    {
        self::assertInstanceOf(AbstractRawDataCapableRepository::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsDirectPersist(): void
    {
        self::assertInstanceOf(DirectPersist::class, $this->subject);
    }
}
