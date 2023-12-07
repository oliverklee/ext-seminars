<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait
 */
final class MakeInstanceTraitTest extends UnitTestCase
{
    use MakeInstanceTrait;

    protected function tearDown(): void
    {
        $this->purgeMockedInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function mockedInstancesListIsEmptyInitially(): void
    {
        self::assertSame([], $this->mockClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsClassNameToList(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \stdClass::class;

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame([$mockedClassName], $this->mockClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsInstanceToTypo3InstanceBuffer(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \stdClass::class;

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesClassnameFromList(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \stdClass::class;
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();
        // manually purge the TYPO3 FIFO here, as purgeMockedInstances() is not tested for that yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertSame([], $this->mockClassNames);
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesInstanceFromTypoInstanceBuffer(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \stdClass::class;
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();

        self::assertNotSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }
}
