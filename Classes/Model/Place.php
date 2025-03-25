<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a place.
 */
class Place extends AbstractModel
{
    /**
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    public function getFullAddress(): string
    {
        return $this->getAsString('address');
    }

    /**
     * @return string the city name, will not be empty
     */
    public function getCity(): string
    {
        return $this->getAsString('city');
    }
}
