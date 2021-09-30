<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Mapper\BackEndUserMapper;
use OliverKlee\Seminars\Model\BackEndUser;

/**
 * @covers \OliverKlee\Seminars\Mapper\BackEndUserMapper
 */
final class BackEndUserMapperTest extends UnitTestCase
{
    /**
     * @var BackEndUserMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BackEndUserMapper();
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

        self::assertInstanceOf(BackEndUser::class, $model);
    }
}
