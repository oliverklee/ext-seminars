<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * @covers \Tx_Seminars_Mapper_FrontEndUserGroup
 */
final class FrontEndUserGroupMapperTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Mapper_FrontEndUserGroup
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Mapper_FrontEndUserGroup();
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
        self::assertInstanceOf(\OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function createFrontEndUserGroupModel()
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUserGroup::class, $model);
    }
}
