<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\OldModel\LegacyCategory;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyCategory
 */
final class CategoryTest extends UnitTestCase
{
    /**
     * @var LegacyCategory
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new LegacyCategory();
    }

    /**
     * @test
     */
    public function isAbstractModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass(): void
    {
        $result = LegacyCategory::fromData([]);

        self::assertInstanceOf(LegacyCategory::class, $result);
    }

    /**
     * @test
     */
    public function getTitleReturnsTitle(): void
    {
        $title = 'Test category';
        $subject = LegacyCategory::fromData(['title' => $title]);

        self::assertSame($title, $subject->getTitle());
    }

    /**
     * @test
     */
    public function getIconReturnsIcon(): void
    {
        $icon = 'foo.gif';
        $subject = LegacyCategory::fromData(['icon' => $icon]);

        self::assertSame($icon, $subject->getIcon());
    }
}
