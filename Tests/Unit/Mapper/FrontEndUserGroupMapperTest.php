<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\FrontEndUserGroup;

/**
 * @covers \Tx_Seminars_Mapper_FrontEndUserGroup
 */
final class FrontEndUserGroupMapperTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Mapper_FrontEndUserGroup
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_Mapper_FrontEndUserGroup();
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
    public function createFrontEndUserGroupModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(FrontEndUserGroup::class, $model);
    }
}
