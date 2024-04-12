<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents an organizer.
 */
class Organizer extends AbstractModel implements MailRole
{
    /**
     * @return string our name, will not be empty
     */
    public function getName(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $name our name to set, must not be empty
     */
    public function setName(string $name): void
    {
        if ($name == '') {
            throw new \InvalidArgumentException('The parameter $name must not be empty.', 1333296852);
        }

        $this->setAsString('title', $name);
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
     * @return string our email address, will not be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * @param string $emailAddress our email address, must not be empty
     */
    public function setEmailAddress(string $emailAddress): void
    {
        if ($emailAddress == '') {
            throw new \InvalidArgumentException('The parameter $emailAddress must not be empty.', 1333296861);
        }

        $this->setAsString('email', $emailAddress);
    }

    /**
     * @return string our email footer, may be empty
     */
    public function getEmailFooter(): string
    {
        return $this->getAsString('email_footer');
    }

    /**
     * @param string $emailFooter our email footer, may be empty
     */
    public function setEmailFooter(string $emailFooter): void
    {
        $this->setAsString('email_footer', $emailFooter);
    }

    public function hasEmailFooter(): bool
    {
        return $this->hasString('email_footer');
    }

    public function hasDescription(): bool
    {
        return $this->hasString('description');
    }

    /**
     * @return string the description of the organizer in raw format, will be empty if organizer has no description
     */
    public function getDescription(): string
    {
        return $this->getAsString('description');
    }

    /**
     * @return string our name, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getName();
    }
}
