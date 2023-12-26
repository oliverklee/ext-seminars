<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a specific venue, e.g., a hotel or a university.
 */
class Venue extends AbstractEntity
{
    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected $title = '';

    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected $contactPerson = '';

    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected $emailAddress = '';

    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected $phoneNumber = '';

    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 1024})
     */
    protected $fullAddress = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $name): void
    {
        $this->title = $name;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getFullAddress(): string
    {
        return $this->fullAddress;
    }

    public function setFullAddress(string $fullAddress): void
    {
        $this->fullAddress = $fullAddress;
    }
}
