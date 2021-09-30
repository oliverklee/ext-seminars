<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Seminars\Model\FrontEndUserGroup;

/**
 * @covers \OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper
 */
final class FrontEndUserGroupMapperTest extends UnitTestCase
{
    /**
     * @var FrontEndUserGroupMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUserGroupMapper();
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
