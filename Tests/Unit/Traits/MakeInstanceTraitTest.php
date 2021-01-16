<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait
 *
 * @author Stefano Kowalke <info@arroba-it.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class MakeInstanceTraitTest extends UnitTestCase
{
    use MakeInstanceTrait;

    /**
     * @test
     */
    public function mockedInstancesListIsEmptyInitially()
    {
        self::assertSame([], $this->mockClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsClassNameToList()
    {
        $mockedInstance = new \stdClass();
        $mockedClassName = \stdClass::class;

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame([$mockedClassName], $this->mockClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsInstanceToTypo3InstanceBuffer()
    {
        $mockedInstance = new \stdClass();
        $mockedClassName = \stdClass::class;

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesClassnameFromList()
    {
        $mockedInstance = new \stdClass();
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
    public function purgeMockedInstancesRemovesInstanceFromTypoInstanceBuffer()
    {
        $mockedInstance = new \stdClass();
        $mockedClassName = \stdClass::class;
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();

        self::assertNotSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }
}
