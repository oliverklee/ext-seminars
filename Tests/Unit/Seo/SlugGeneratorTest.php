<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Seo\SlugGenerator;

/**
 * @covers \OliverKlee\Seminars\Seo\SlugGenerator
 */
final class SlugGeneratorTest extends UnitTestCase
{
    /**
     * @var SlugGenerator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SlugGenerator();
    }

    /**
     * @test
     */
    public function getPrefixAlwaysReturnsAnEmptyString(): void
    {
        self::assertSame('', $this->subject->getPrefix());
    }

    /**
     * @test
     */
    public function generateSlugForEmptyRecordReturnsEmptyString(): void
    {
        $result = $this->subject->generateSlug(['record' => []]);

        self::assertSame('', $result);
    }

    /**
     * @return array<string,array{0: string|int}>
     */
    public static function emptyUidDataProvider(): array
    {
        return [
            'empty string' => [''],
            'new record placeholder' => ['NEW56fe7404a3a455'],
            'zero' => [0],
        ];
    }

    /**
     * @test
     *
     * @param string|int $uid
     * @dataProvider emptyUidDataProvider
     */
    public function generateSlugForEmptyUidReturnsEmptyString($uid): void
    {
        $result = $this->subject->generateSlug(['record' => ['uid' => $uid]]);

        self::assertSame('', $result);
    }
}
