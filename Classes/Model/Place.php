<?php

declare(strict_types=1);

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\Country;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a place.
 */
class Tx_Seminars_Model_Place extends AbstractModel implements Titled
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
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296894);
        }

        $this->setAsString('title', $title);
    }

    /**
     * @return string our address, might be empty
     */
    public function getAddress(): string
    {
        return $this->getAsString('address');
    }

    /**
     * @param string $address our address to set, may be empty
     */
    public function setAddress(string $address): void
    {
        $this->setAsString('address', $address);
    }

    public function hasAddress(): bool
    {
        return $this->hasString('address');
    }

    /**
     * @return string the ZIP code, might be empty
     */
    public function getZip(): string
    {
        return $this->getAsString('zip');
    }

    /**
     * @param string $zip our ZIP code, may be empty
     */
    public function setZip(string $zip): void
    {
        $this->setAsString('zip', $zip);
    }

    public function hasZip(): bool
    {
        return $this->hasString('zip');
    }

    /**
     * @return string the city name, will not be empty
     */
    public function getCity(): string
    {
        return $this->getAsString('city');
    }

    /**
     * @param string $city our city name, must not be empty
     */
    public function setCity(string $city): void
    {
        if ($city == '') {
            throw new \InvalidArgumentException('The parameter $city must not be empty.', 1333296904);
        }

        $this->setAsString('city', $city);
    }

    public function getCountry(): ?Country
    {
        $countryCode = $this->getAsString('country');
        if ($countryCode == '') {
            return null;
        }

        try {
            $mapper = MapperRegistry::get(CountryMapper::class);
            $country = $mapper->findByIsoAlpha2Code($countryCode);
        } catch (NotFoundException $exception) {
            $country = null;
        }

        return $country;
    }

    public function setCountry(Country $country = null): void
    {
        $countryCode = ($country !== null) ? $country->getIsoAlpha2Code() : '';

        $this->setAsString('country', $countryCode);
    }

    public function hasCountry(): bool
    {
        return $this->getCountry() !== null;
    }

    /**
     * @return string our homepage, may be empty
     */
    public function getHomepage(): string
    {
        return $this->getAsString('homepage');
    }

    /**
     * @param string $homepage our homepage, may be empty
     */
    public function setHomepage(string $homepage): void
    {
        $this->setAsString('homepage', $homepage);
    }

    public function hasHomepage(): bool
    {
        return $this->hasString('homepage');
    }

    /**
     * @return string our directions, might be empty
     */
    public function getDirections(): string
    {
        return $this->getAsString('directions');
    }

    /**
     * @param string $directions our directions to set, may be empty
     */
    public function setDirections(string $directions): void
    {
        $this->setAsString('directions', $directions);
    }

    public function hasDirections(): bool
    {
        return $this->hasString('directions');
    }

    public function getOwner(): ?FrontEndUser
    {
        /** @var FrontEndUser|null $owner */
        $owner = $this->getAsModel('owner');

        return $owner;
    }

    public function setOwner(FrontEndUser $frontEndUser): void
    {
        $this->set('owner', $frontEndUser);
    }

    /**
     * @return string our notes, may be empty
     */
    public function getNotes(): string
    {
        return $this->getAsString('notes');
    }

    /**
     * @param string $notes our notes to set, might be empty
     */
    public function setNotes(string $notes): void
    {
        $this->setAsString('notes', $notes);
    }
}
