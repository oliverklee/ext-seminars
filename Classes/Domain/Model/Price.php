<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * This class represents a single price for an event, e.g., "early-bird price".
 *
 * At the moment, instances of this class are not persisted.
 */
class Price
{
    /**
     * @var non-empty-string
     */
    public const PRICE_STANDARD = 'price_regular';

    /**
     * @var non-empty-string
     */
    public const PRICE_EARLY_BIRD = 'price_regular_early';

    /**
     * @var non-empty-string
     */
    public const PRICE_SPECIAL = 'price_special';

    /**
     * @var non-empty-string
     */
    public const PRICE_SPECIAL_EARLY_BIRD = 'price_special_early';

    /**
     * @var float
     */
    private $amount;

    /**
     * the full localization key in the seminars extension
     *
     * @var non-empty-string
     */
    private $labelKey;

    /**
     * @var self::PRICE_*
     */
    private $priceCode;

    /**
     * @param float $amount
     * @param non-empty-string $labelKey the full localization key in the seminars extension
     * @param self::PRICE_* $priceCode
     */
    public function __construct(float $amount, string $labelKey, string $priceCode)
    {
        $this->amount = $amount;
        $this->labelKey = $labelKey;
        $this->priceCode = $priceCode;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return non-empty-string the full localization key in the seminars extension
     */
    public function getLabelKey(): string
    {
        return $this->labelKey;
    }

    /**
     * @return self::PRICE_*
     */
    public function getPriceCode(): string
    {
        return $this->priceCode;
    }
}
