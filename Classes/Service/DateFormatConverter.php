<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

/**
 * Convers date and time formats from the old `sprintf` format to a format usable by `date`.
 *
 * @internal
 *
 * @deprecated #2342 will be removed in seminars 6.0
 */
class DateFormatConverter
{
    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const CONVERSION = [
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
     * Converts the given `sprintf` format to a format usable by `date`.
     */
    public static function convert(string $sprintfFormat): string
    {
        $from = \array_keys(self::CONVERSION);
        $to = \array_values(self::CONVERSION);

        return \str_replace($from, $to, $sprintfFormat);
    }
}
