<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Service\DateFormatConverter;

/**
 * @covers \OliverKlee\Seminars\Service\DateFormatConverter
 */
final class DateFormatConverterTest extends UnitTestCase
{
    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const FORMATS = [
        '%a' => 'D',
        '%A' => 'l',
        '%d' => 'd',
        '%e' => 'n',
        '%V' => 'W',
        '%b' => 'M',
        '%B' => 'F',
        '%h' => 'M',
        '%m' => 'm',
        '%Y' => 'Y',
        '%g' => 'y',
        '%H' => 'H',
        '%k' => 'G',
        '%I' => 'h',
        '%l' => 'g',
        '%M' => 'i',
        '%p' => 'A',
        '%P' => 'a',
    ];

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public function oldToNewFormatDataProvider(): array
    {
        $result = [];

        foreach (self::FORMATS as $oldFormat => $newFormat) {
            $result[$oldFormat] = [$oldFormat, $newFormat];
        }

        return $result;
    }

    /**
     * @test
     *
     * @param non-empty-string $old
     * @param non-empty-string $new
     *
     * @dataProvider oldToNewFormatDataProvider
     */
    public function convertsSingleSegments(string $old, string $new): void
    {
        $result = DateFormatConverter::convert($old);

        self::assertSame($new, $result);
    }

    /**
     * @test
     */
    public function convertsCompleteGermanDateFormat(): void
    {
        $result = DateFormatConverter::convert('%d.%m.%Y');

        self::assertSame('d.m.Y', $result);
    }

    /**
     * @test
     */
    public function convertsCompleteTimeFormat(): void
    {
        $result = DateFormatConverter::convert('%H:%M');

        self::assertSame('H:i', $result);
    }

    /**
     * @test
     */
    public function convertsDateAndTime(): void
    {
        $result = DateFormatConverter::convert('%d.%m.%Y %H:%M');

        self::assertSame('d.m.Y H:i', $result);
    }

    /**
     * @test
     */
    public function leavesOtherCharactersUntouched(): void
    {
        $string = '1234567890,.-;:_#+"^"!§$%&/()=?';

        $result = DateFormatConverter::convert($string);

        self::assertSame($string, $result);
    }
}
