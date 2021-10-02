<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a payment method.
 */
class PaymentMethod extends AbstractModel implements Titled
{
    /**
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $title our title to set, must not be empty
     */
    public function setTitle(string $title): void
    {
        if ($title == '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296882);
        }

        $this->setAsString('title', $title);
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
