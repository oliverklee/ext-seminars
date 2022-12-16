<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Seminars\Model\FrontEndUserGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper
 */
final class FrontEndUserGroupMapperTest extends TestCase
{
    /**
     * @var FrontEndUserGroupMapper the object to test
     */
    private $subject;

    /**
     * @var TestingFramework the testing framework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUserGroupMapper();
        $this->testingFramework = new TestingFramework('tx_seminars');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////////////////
    // Tests for the basic functionality
    //////////////////////////////////////

    /**
     * @test
     */
    public function mapperForGhostReturnsSeminarsFrontEndUserGroupInstance(): void
    {
        self::assertInstanceOf(FrontEndUserGroup::class, $this->subject->getNewGhost());
    }
}
