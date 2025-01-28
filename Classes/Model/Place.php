<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\Country;

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
     * @return string the ZIP code, might be empty
     */
    public function getZip(): string
    {
        return $this->getAsString('zip');
    }

    /**
     * @return string the city name, will not be empty
     */
    public function getCity(): string
    {
        return $this->getAsString('city');
    }

    public function getCountry(): ?Country
    {
        $countryCode = $this->getAsString('country');
        if ($countryCode === '') {
            return null;
        }

        try {
            $country = MapperRegistry::get(CountryMapper::class)->findByIsoAlpha2Code($countryCode);
        } catch (NotFoundException $exception) {
            $country = null;
        }

        return $country;
    }

    public function hasCountry(): bool
    {
        return $this->getCountry() !== null;
    }
}
