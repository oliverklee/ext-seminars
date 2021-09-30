<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\BackEndUserGroup;

/**
 * @covers \Tx_Seminars_Mapper_BackEndUserGroup
 */
final class BackEndUserGroupMapperTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Mapper_BackEndUserGroup
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_Mapper_BackEndUserGroup();
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
    public function createBackEndUserGroupModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(BackEndUserGroup::class, $model);
    }
}
