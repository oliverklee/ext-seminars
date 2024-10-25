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
     * @return string our email address, will not be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
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
}
