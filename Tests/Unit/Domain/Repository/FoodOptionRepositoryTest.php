<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Repository;

use OliverKlee\Seminars\Domain\Repository\FoodOptionRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Repository\FoodOptionRepository
 */
final class FoodOptionRepositoryTest extends UnitTestCase
{
    private FoodOptionRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if (\interface_exists(ObjectManagerInterface::class)) {
            $objectManagerStub = $this->createStub(ObjectManagerInterface::class);
            $this->subject = new FoodOptionRepository($objectManagerStub);
        } else {
            $this->subject = new FoodOptionRepository();
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
