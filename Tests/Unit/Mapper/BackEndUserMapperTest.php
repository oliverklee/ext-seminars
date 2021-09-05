<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * @covers \Tx_Seminars_Mapper_BackEndUser
 */
final class BackEndUserMapperTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Mapper_BackEndUser
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Mapper_BackEndUser();
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
        self::assertInstanceOf(\OliverKlee\Oelib\Mapper\BackEndUserMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function createBackEndUserModel()
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(\Tx_Seminars_Model_BackEndUser::class, $model);
    }
}
