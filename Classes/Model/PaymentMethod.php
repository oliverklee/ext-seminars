<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a payment method.
 */
class PaymentMethod extends AbstractModel
{
    /**
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @return string our description, might be empty
     */
    public function getDescription(): string
    {
        return $this->getAsString('description');
    }

    /**
     * @param string $description our description to set, may be empty
     */
    public function setDescription(string $description): void
    {
        $this->setAsString('description', $description);
    }

    public function hasDescription(): bool
    {
        return $this->hasString('description');
    }
}
