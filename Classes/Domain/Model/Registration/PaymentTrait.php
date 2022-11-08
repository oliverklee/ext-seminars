<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Registration;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * Payment-related for the `Registration` model.
 *
 * @mixin Registration
 */
trait PaymentTrait
{
    /**
     * @var string|null
     * @phpstan-var EventInterface::PRICE_*|null
     * @Extbase\Validate("StringLength", options={"maximum": 32})
     */
    protected $priceCode;

    /**
     * @var float
     */
    protected $totalPrice = 0.0;

    /**
     * @return EventInterface::PRICE_*
     */
    public function getPriceCode(): ?string
    {
        return $this->priceCode;
    }

    /**
     * @param EventInterface::PRICE_* $priceCode
     */
    public function setPriceCode(string $priceCode): void
    {
        $this->priceCode = $priceCode;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }
}
