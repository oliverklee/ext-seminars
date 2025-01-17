<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a speaker for an event.
 */
class Speaker extends AbstractEntity implements MailRole
{
    /**
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected string $name = '';

    /**
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected string $emailAddress = '';

    /**
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected string $organization = '';

    /**
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected string $homepage = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getOrganization(): string
    {
        return $this->organization;
    }

    public function setOrganization(string $organization): void
    {
        $this->organization = $organization;
    }

    public function getHomepage(): string
    {
        return $this->homepage;
    }

    public function setHomepage(string $homepage): void
    {
        $this->homepage = $homepage;
    }
}
