<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Repository;

use OliverKlee\Seminars\Domain\Repository\EventTypeRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Repository\EventTypeRepository
 */
final class EventTypeRepositoryTest extends UnitTestCase
{
    /**
     * @var EventTypeRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if (\interface_exists(ObjectManagerInterface::class)) {
            $objectManagerStub = $this->createStub(ObjectManagerInterface::class);
            $this->subject = new EventTypeRepository($objectManagerStub);
        } else {
            $this->subject = new EventTypeRepository();
        }
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }
}
