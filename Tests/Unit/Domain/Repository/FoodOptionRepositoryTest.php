<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Repository;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Repository\FoodOptionRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @covers \OliverKlee\Seminars\Domain\Repository\FoodOptionRepository
 */
final class FoodOptionRepositoryTest extends UnitTestCase
{
    /**
     * @var FoodOptionRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManagerStub = $this->createMock(ObjectManagerInterface::class);
        $this->subject = new FoodOptionRepository($objectManagerStub);
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }
}
