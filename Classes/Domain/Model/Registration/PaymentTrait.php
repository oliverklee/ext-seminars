<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Registration;

use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\Price;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * Payment-related for the `Registration` model.
 *
 * @phpstan-require-extends Registration
 */
trait PaymentTrait
{
    /**
     * @var string
     * @phpstan-var Price::PRICE_*
     * @Extbase\Validate("StringLength", options={"maximum": 32})
     */
    protected $priceCode = Price::PRICE_STANDARD;

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 255})
     */
    protected $humanReadablePrice = '';

    /**
     * @var float
     */
    protected $totalPrice = 0.0;

    /**
     * @var \OliverKlee\Seminars\Domain\Model\PaymentMethod|null
     * @phpstan-var PaymentMethod|LazyLoadingProxy|null
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $paymentMethod;

    /**
     * @return Price::PRICE_*
     */
    public function getPriceCode(): string
    {
        $priceCode = $this->priceCode;
        if (!Price::isPriceCodeValid($priceCode)) {
            $priceCode = Price::PRICE_STANDARD;
        }

        return $priceCode;
    }

    /**
     * @param Price::PRICE_* $priceCode
     */
    public function setPriceCode(string $priceCode): void
    {
        $this->priceCode = $priceCode;
    }

    public function getHumanReadablePrice(): string
    {
        return $this->humanReadablePrice;
    }

    public function setHumanReadablePrice(string $humanReadablePrice): void
    {
        $this->humanReadablePrice = $humanReadablePrice;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        $paymentMethod = $this->paymentMethod;
        if ($paymentMethod instanceof LazyLoadingProxy) {
            $paymentMethod = $paymentMethod->_loadRealInstance();
            if ($paymentMethod instanceof PaymentMethod) {
                $this->paymentMethod = $paymentMethod;
            }
        }

        return $paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }
}
