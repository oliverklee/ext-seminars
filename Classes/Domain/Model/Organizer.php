<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a organizer of an event. It can be a company or a person.
 */
class Organizer extends AbstractEntity implements MailRole
{
    /**
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected string $name = '';

    /**
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected string $emailAddress = '';

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
}
