<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\FrontEndUser;

/**
 * @covers \Tx_Seminars_Mapper_FrontEndUser
 */
final class FrontEndUserMapperTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_Mapper_FrontEndUser();
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
    public function createFrontEndUserModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(FrontEndUser::class, $model);
    }
}
