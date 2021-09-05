<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * @covers \Tx_Seminars_Mapper_BackEndUserGroup
 */
final class BackEndUserGroupMapperTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Mapper_BackEndUserGroup
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Mapper_BackEndUserGroup();
    }

    /**
     * @test
     */
    public function isMapper()
    {
        self::assertInstanceOf(AbstractDataMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function isOelibMapper()
    {
        self::assertInstanceOf(\OliverKlee\Oelib\Mapper\BackEndUserGroupMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function createBackEndUserGroupModel()
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(\Tx_Seminars_Model_BackEndUserGroup::class, $model);
    }
}
