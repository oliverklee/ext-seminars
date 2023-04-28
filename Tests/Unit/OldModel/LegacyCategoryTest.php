<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\OldModel\LegacyCategory;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyCategory
 */
final class LegacyCategoryTest extends UnitTestCase
{
    /**
     * @var LegacyCategory
     */
    private $subject;

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
    public function fromDataCreatesCategoryInstance(): void
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

    public function hasIconForNoDataReturnsFalse(): void
    {
        $subject = LegacyCategory::fromData([]);

        self::assertFalse($subject->hasIcon());
    }

    /**
     * @return array<string, array{0: string|int}>
     */
    public function noIconDataProvider(): array
    {
        return [
            'empty string' => [''],
            'file name before migration' => ['icon.png'],
            'zero as string' => ['0'],
            'zero as integer' => [0],
        ];
    }

    /**
     * @test
     *
     * @param string|int $icon
     *
     * @dataProvider noIconDataProvider
     */
    public function hasIconForNoIconReturnsFalse($icon): void
    {
        $subject = LegacyCategory::fromData(['icon' => $icon]);

        self::assertFalse($subject->hasIcon());
    }

    public function hasIconWithPositiveNumberOfIconsReturnsTrue(): void
    {
        $subject = LegacyCategory::fromData(['icon' => 1]);

        self::assertTrue($subject->hasIcon());
    }
}
