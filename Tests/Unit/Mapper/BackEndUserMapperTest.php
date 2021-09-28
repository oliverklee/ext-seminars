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

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_Mapper_BackEndUser();
    }

    /**
     * @test
     */
    public function isMapper(): void
    {
        self::assertInstanceOf(AbstractDataMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function createBackEndUserModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(\Tx_Seminars_Model_BackEndUser::class, $model);
    }
}
