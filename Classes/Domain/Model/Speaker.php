<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a speaker for an event.
 */
class Speaker extends AbstractEntity implements MailRole
{
    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 255})
     */
    protected $name = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 255})
     */
    protected $emailAddress = '';

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
