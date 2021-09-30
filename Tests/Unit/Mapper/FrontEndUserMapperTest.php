<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUser;

/**
 * @covers \OliverKlee\Seminars\Mapper\FrontEndUserMapper
 */
final class FrontEndUserMapperTest extends UnitTestCase
{
    /**
     * @var FrontEndUserMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUserMapper();
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
