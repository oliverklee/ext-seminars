<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Registration;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

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
     * @var \OliverKlee\Seminars\Domain\Model\PaymentMethod|null
     * @phpstan-var PaymentMethod|LazyLoadingProxy|null
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $paymentMethod;

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

    public function getPaymentMethod(): ?PaymentMethod
    {
        $paymentMethod = $this->paymentMethod;
        if ($paymentMethod instanceof LazyLoadingProxy) {
            $paymentMethod = $paymentMethod->_loadRealInstance();
            \assert($paymentMethod instanceof PaymentMethod);
            $this->paymentMethod = $paymentMethod;
        }

        return $paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }
}
