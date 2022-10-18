<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use TYPO3\CMS\Extbase\Annotation as Extbase;

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
}
