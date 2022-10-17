<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Repository;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Repository\EventRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @covers \OliverKlee\Seminars\Domain\Repository\EventRepository
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

        $objectManagerStub = $this->prophesize(ObjectManagerInterface::class)->reveal();
        $this->subject = new EventRepository($objectManagerStub);
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }
}
