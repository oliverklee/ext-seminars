<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This trait provides methods that are useful for `EventTopic`s, and usually also `SingleEvent`s.
 *
 * @mixin Event
 */
trait EventTopicTrait
{
    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 16383})
     */
    protected $description = '';

    /**
     * @var float
     */
    protected $standardPrice = 0.0;

    /**
     * @var float
     */
    protected $earlyBirdPrice = 0.0;

    /**
     * @var float
     */
    protected $specialPrice = 0.0;

    /**
     * @var float
     */
    protected $specialEarlyBirdPrice = 0.0;

    /**
     * @var \OliverKlee\Seminars\Domain\Model\EventType|null
     * @phpstan-var EventType|LazyLoadingProxy|null
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $eventType;

    /**
     * @var bool
     */
    protected $additionalTermsAndConditions = false;

    /**
     * @var bool
     */
    protected $multipleRegistrationPossible = false;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\PaymentMethod>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $paymentMethods;

    private function initializeEventTopic(): void
    {
        $this->paymentMethods = new ObjectStorage();
    }

    public function getDisplayTitle(): string
    {
        return $this->getInternalTitle();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStandardPrice(): float
    {
        return $this->standardPrice;
    }

    public function setStandardPrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('The price must be >= 0.0.', 1666112500);
        }

        $this->standardPrice = $price;
    }

    public function getEarlyBirdPrice(): float
    {
        return $this->earlyBirdPrice;
    }

    public function setEarlyBirdPrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('The price must be >= 0.0.', 1666112478);
        }

        $this->earlyBirdPrice = $price;
    }

    public function getSpecialPrice(): float
    {
        return $this->specialPrice;
    }

    public function setSpecialPrice(float $specialPrice): void
    {
        $this->specialPrice = $specialPrice;
    }

    public function getSpecialEarlyBirdPrice(): float
    {
        return $this->specialEarlyBirdPrice;
    }

    public function setSpecialEarlyBirdPrice(float $specialEarlyBirdPrice): void
    {
        $this->specialEarlyBirdPrice = $specialEarlyBirdPrice;
    }

    public function getEventType(): ?EventType
    {
        $eventType = $this->eventType;
        if ($eventType instanceof LazyLoadingProxy) {
            $eventType = $eventType->_loadRealInstance();
            \assert($eventType instanceof EventType);
            $this->eventType = $eventType;
        }

        return $eventType;
    }

    public function setEventType(?EventType $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function hasAdditionalTermsAndConditions(): bool
    {
        return $this->additionalTermsAndConditions;
    }

    public function setAdditionalTermsAndConditions(bool $additionalTermsAndConditions): void
    {
        $this->additionalTermsAndConditions = $additionalTermsAndConditions;
    }

    public function isMultipleRegistrationPossible(): bool
    {
        return $this->multipleRegistrationPossible;
    }

    public function setMultipleRegistrationPossible(bool $multipleRegistrationPossible): void
    {
        $this->multipleRegistrationPossible = $multipleRegistrationPossible;
    }

    /**
     * @return ObjectStorage<PaymentMethod>
     */
    public function getPaymentMethods(): ObjectStorage
    {
        return $this->paymentMethods;
    }

    /**
     * @param ObjectStorage<PaymentMethod> $paymentMethods
     */
    public function setPaymentMethods(ObjectStorage $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }
}
