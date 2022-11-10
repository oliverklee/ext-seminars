<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Price;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Price
 */
final class PriceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getAmountReturnsAmountProvidedToConstructor(): void
    {
        $amount = 149.95;
        $price = new Price($amount, 'labelKey', 'price_regular');

        self::assertSame($amount, $price->getAmount());
    }

    /**
     * @test
     */
    public function getLabelKeyReturnsLabelKeyProvidedToConstructor(): void
    {
        $labelKey = 'some-key';
        $price = new Price(0.0, $labelKey, 'price_regular');

        self::assertSame($labelKey, $price->getLabelKey());
    }

    /**
     * @test
     */
    public function getPriceCodeKeyReturnsPriceCodeProvidedToConstructor(): void
    {
        $priceCode = 'price_regular_early';
        $price = new Price(0.0, 'labelKey', $priceCode);

        self::assertSame($priceCode, $price->getPriceCode());
    }
}
